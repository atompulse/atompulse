<?php
namespace Atompulse\Bundle\RanBundle\Command;

use Atompulse\Bundle\RanBundle\Service\Ran\RanRouteProcessor;
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

            $ranGuiTree = [];
            $ranSystemTree = [
                'hierarchy' => [],
                'requirements' => []
            ];

            /** @var $routeConfiguration \Symfony\Component\Routing\Route */
            foreach ($allRoutes as $routeName => $routeConfiguration) {

                // store last analyzed route
                $lastAnalyzedRoute = [
                    $routeName => $routeConfiguration
                ];

                // check if route is secured by RAN
                if ($routeConfiguration->hasOption('ran')) {

                    $ran = $routeConfiguration->getOption('ran');

                    if (!is_array($ran)) {
                        continue;
                    }

                    // process the RAN configuration of the route
                    $ranItem = RanRouteProcessor::process($routeName, $routeConfiguration);

                    // initialize group
                    if (!isset($ranGuiTree[$ranItem->group])) {
                        $ranGuiTree[$ranItem->group] = [
                            'role' => $ranItem->name,
                            'roles' => []
                        ];

                        // create group role hierarchy
                        $ranSystemTree['hierarchy'][$ranItem->group] = [];
                    }

                    // decide by context where to add this item
                    if ($ranItem->context === 'internal') {
                        // add it only to system tree => will be checked by security system but not available on gui
                        // this is used for inheriting existing role

                        // system tree
                        if (!in_array($ranItem->name, $ranSystemTree['hierarchy'][$ranItem->group])) {
                            $ranSystemTree['hierarchy'][$ranItem->group][] = $ranItem->name;
                        }
                        $ranSystemTree['requirements'][$routeName]['group'] = $ranItem->group;
                        $ranSystemTree['requirements'][$routeName]['single'] = $ranItem->name;
                        $ranSystemTree['requirements'][$routeName]['granted'] = $ranItem->granted;
                    } else {
                        // add it everywhere

                        // gui tree
                        $ranGuiTree[$ranItem->group]['roles'][$routeName] = [
                            'role' => $ranItem->name,
                            'label' => $ranItem->label,
                            'scope' => $ranItem->context
                        ];
                        // system tree
                        $ranSystemTree['hierarchy'][$ranItem->group][] = $ranItem->name;
                        $ranSystemTree['requirements'][$routeName]['group'] = $ranItem->group;
                        $ranSystemTree['requirements'][$routeName]['single'] = $ranItem->name;
                        $ranSystemTree['requirements'][$routeName]['granted'] = $ranItem->granted;
                    }
                }
            }

            $output->writeln("<info>Processed <fg=blue>[" . count($ranSystemTree['requirements']) . "]</fg=blue> routes.\nTrying to save configs..<info>");

            // prepare symfony parameters format
            $ranGuiData = Yaml::dump(['parameters' => ['ran_gui' => $ranGuiTree]], 6, 2);
            $ranSystemData = Yaml::dump(['parameters' => ['ran_sys' => $ranSystemTree]], 5, 2);

            // setup save path
            $savePath = $this->getContainer()->getParameter('ran')['generator']['output'];

            if (file_exists($savePath)) {
                $err = file_put_contents($savePath . '/role_access_names_gui.yml', $ranGuiData);
                $err = $err && file_put_contents($savePath . '/role_access_names_system.yml', $ranSystemData);
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
