<?php
namespace Atompulse\Bundle\RanBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;


/**
 * Class BuildRanCommand
 * @package Atompulse\Bundle\RanBundle\Command
 *
 * @author Petru Cojocar <petru.cojocar@gmail.com>
 */
class BuildRanCommand extends ContainerAwareCommand
{

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('ap:ran:build')
            ->setDescription('Build Role Access Names');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Analyzing routes..</info>");
        $lastAnalyzedRoute = [];

        try {
            /** @var $router \Symfony\Component\Routing\Router */
            $router = $this->getContainer()->get('router');
            /** @var $collection \Symfony\Component\Routing\RouteCollection */
            $collection = $router->getRouteCollection();
            $allRoutes = $collection->all();

            $output->writeln("<info>Found a total of: <fg=blue>[" . count($allRoutes) . "]</fg=blue> routes.\nStarting to process routes..</info>");

            $roleAccessNamesGui = [];
            $roleAccessListSystem = [
                'hierarchy' => [],
                'requirements' => []
            ];

            $defaultScope = 'internal';

            /** @var $settings \Symfony\Component\Routing\Route */
            foreach ($allRoutes as $routeName => $settings) {
                // store last analyzed route
                $lastAnalyzedRoute = [
                    $routeName => $settings
                ];
                // check if route is secured by RAN
                if ($settings->hasOption('ran')) {

                    $ran = $settings->getOption('ran');

                    if (!is_array($ran)) {
                        continue;
                    }

                    // analyze RAN : [group, label, scope, role]
                    $routeNameData = explode('_', strtoupper($routeName));

                    if (is_numeric(key($ran))) {
                        // group mandatory
                        $group = $ran[0];
                        // label extraction: if label not given then will use the route name starting from the second word and humanize it
                        $label = isset($ran[1]) ? $ran[1] : ucfirst(strtolower(implode(' ', array_slice($routeNameData, 1))));
                        // scope extraction : if no scope defined but label defined then use 'action' otherwise use defaultScope
                        $scope = isset($ran[2]) ? $ran[2] : (isset($ran[1]) ? 'action' : $defaultScope);
                        // role extraction: if role not explicit then use the exact route name
                        $role = isset($ran[3]) ? $ran[3] : $routeName;
                    } else {
                        print_r($ran);
                        // group mandatory
                        $group = $ran['group'];
                        // label extraction: if label not given then will use the route name starting from the second word and humanize the it
                        $label = isset($ran['label']) ? $ran['label'] : ucfirst(strtolower(implode(' ', array_slice($routeNameData, 1))));
                        // scope extraction : if no scope defined but label defined then use 'action' otherwise use defaultScope
                        $scope = isset($ran['scope']) ? $ran['scope'] : (isset($ran['label']) ? 'action' : $defaultScope);
                        // role extraction: if role not explicit then use the exact route name
                        $role = isset($ran['role']) ? $ran['role'] : $routeName;
                    }

                    // final role access name
                    $singleRole = 'RAN_' . strtoupper($role);
                    // final role group access name
                    $groupRole = 'RAN_' . strtoupper($group) . '_ALL';

                    // initialize group if was not already in
                    if (!isset($roleAccessNamesGui[$group])) {
                        $roleAccessNamesGui[$group] = [
                            'role' => $groupRole,
                            'roles' => []
                        ];

                        // create group role hierarchy
                        $roleAccessListSystem['hierarchy'][$groupRole] = [];
                    }


                    // assemble RAN item
                    $ranItem = [
                        'role' => $singleRole,
                        'label' => $label,
                        'scope' => $scope
                    ];

                    // decide by scope where to add this item
                    switch ($scope) {
                        // add it only to system tree => will be checked by security system but not available on gui
                        // this is used for inheriting existing role
                        case 'internal' :
                            // system tree
                            if (!in_array($singleRole, $roleAccessListSystem['hierarchy'][$groupRole])) {
                                $roleAccessListSystem['hierarchy'][$groupRole][] = $singleRole;
                            }
                            $roleAccessListSystem['requirements'][$routeName]['group'] = $groupRole;
                            $roleAccessListSystem['requirements'][$routeName]['single'] = $singleRole;
                            break;
                        // add it everywhere
                        case 'action' :
                            // gui tree
                            $roleAccessNamesGui[$group]['roles'][$routeName] = $ranItem;
                            // system tree
                            $roleAccessListSystem['hierarchy'][$groupRole][] = $singleRole;
                            $roleAccessListSystem['requirements'][$routeName]['group'] = $groupRole;
                            $roleAccessListSystem['requirements'][$routeName]['single'] = $singleRole;
                            break;
                    }
                }
            }

            $output->writeln("<info>Processed <fg=blue>[" . count($roleAccessListSystem['requirements']) . "]</fg=blue> routes.\nTrying to save configs..<info>");

            // prepare symfony parameters format
            $roleAccessNamesGuiData = Yaml::dump(['parameters' => ['ran_gui' => $roleAccessNamesGui]], 6, 2);
            $roleAccessListSystemData = Yaml::dump(['parameters' => ['ran_sys' => $roleAccessListSystem]], 5, 2);

            // setup save path
            $savePath = $this->getContainer()->getParameter('ran')['generator']['output'];

            if (file_exists($savePath)) {
                $err = file_put_contents($savePath . '/role_access_names_gui.yml', $roleAccessNamesGuiData);
                $err = $err && file_put_contents($savePath . '/role_access_names_system.yml', $roleAccessListSystemData);
            } else {
                $output->writeln("<info>Output folder:<info> <fg=red>[" . $savePath . "]</fg=red> was not found");
                $err = true;
            }

            $output->writeln("<info>Process:<info> <fg=blue>[" . ($err ? 'OK' : 'FAILED') . "]</fg=blue>");
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}\n{$e->getFile()}:{$e->getLine()}\nLast route:</error>");
            $output->writeln(var_export($lastAnalyzedRoute, true));
        }
    }

}