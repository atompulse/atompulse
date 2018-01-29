<?php

use Atompulse\Component\FusionInclude\FusionIncludeEngine;

class FusionIncludeEngineTest extends PHPUnit_Framework_TestCase
{
    public function testCheckDefaultGroupAndRevision()
    {
        $fusionIncludeEngine = new FusionIncludeEngine();

        $this->assertEquals(0, $fusionIncludeEngine->getRevision());
        $this->assertEquals('def', $fusionIncludeEngine->getDefaultGroup());
    }

    public function testChangeDefaultRevision()
    {
        $fusionIncludeEngine = new FusionIncludeEngine();

        $fusionIncludeEngine->setRevision(11);

        $this->assertEquals(11, $fusionIncludeEngine->getRevision());
    }

    public function testChangeDefaultGroup()
    {
        $fusionIncludeEngine = new FusionIncludeEngine();

        $fusionIncludeEngine->setDefaultGroup('new-group');

        $this->assertEquals('new-group', $fusionIncludeEngine->getDefaultGroup());
    }
    
    /**
     * @dataProvider providerInitArrayTwo
     */
    public function testGetJs($data)
    {
        $shouldBe = '<script src="http://code.jquery.com/ui/1.11.3/jquery-ui.min.js"></script>';
        $fusionIncludeEngine = new FusionIncludeEngine($data);

        // Here, should be empty result.
        $this->assertEquals('', $fusionIncludeEngine->js());

        $fusionIncludeEngine->load('jquery');
        $this->assertEquals($shouldBe, $fusionIncludeEngine->js());
    }
    
    /**
     * @dataProvider providerInitArrayTwo
     */
    public function testGetCss($data)
    {
        $shouldBe = '<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.11.3/jquery-ui.min.css" />';
        $fusionIncludeEngine = new FusionIncludeEngine($data);

        // Here, should be empty result.
        $this->assertEquals('', $fusionIncludeEngine->css());

        $fusionIncludeEngine->load('jquery');
        $this->assertEquals($shouldBe, $fusionIncludeEngine->css());
    }
    
    /**
     * @dataProvider providerInitArrayTwo
     */
    public function testGetAll($data)
    {
        $shouldBe = '<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.11.3/jquery-ui.min.css" />'."\n".'<script src="http://code.jquery.com/ui/1.11.3/jquery-ui.min.js"></script>';
        $fusionIncludeEngine = new FusionIncludeEngine($data);

        // Here, should be empty result.
        $this->assertEquals("\n", $fusionIncludeEngine->all());

        $fusionIncludeEngine->load('jquery');
        $this->assertEquals($shouldBe, $fusionIncludeEngine->all());
    }
    
    /**
     * @dataProvider providerInitArrayTwo
     */
    public function testDoubleGetAll($data)
    {
        $shouldBe = '<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.11.3/jquery-ui.min.css" />'."\n".'<script src="http://code.jquery.com/ui/1.11.3/jquery-ui.min.js"></script>';
        $fusionIncludeEngine = new FusionIncludeEngine($data);

        // Here, should be empty result.
        $this->assertEquals("\n", $fusionIncludeEngine->all());

        // First...
        $fusionIncludeEngine->load('jquery');
        $this->assertEquals($shouldBe, $fusionIncludeEngine->all());

        // Second...
        $fusionIncludeEngine->load('jquery');
        $this->assertEquals($shouldBe, $fusionIncludeEngine->all());
    }

    public static function providerInitArrayTwo()
    {
        return [
            [
                [
                    [
                        'name'  => 'jquery',
                        'files' => [ 'js' => [ 'http://code.jquery.com/ui/1.11.3/jquery-ui.min.js' ], 'css' => [ 'http://code.jquery.com/ui/1.11.3/jquery-ui.min.css' ] ]
                    ]
                ]
            ]
        ];
    }
    
    public function testAlreadyExists()
    {
        $data = [
            [
                'name'  => 'jquery',
                'files' => [ 'js' => [ 'http://code.jquery.com/ui/1.11.3/jquery-ui.min.js' ], 'css' => [ 'http://code.jquery.com/ui/1.11.3/jquery-ui.min.css' ] ]
            ]
        ];

        $fusionIncludeEngine = new FusionIncludeEngine($data);

        // Here, should be empty result.
        $this->assertEquals(false, $fusionIncludeEngine->alreadyLoaded('jquery'));

        // First load call, should load both CSS and JS
        $fusionIncludeEngine->load('jquery');
        $this->assertEquals(true, $fusionIncludeEngine->alreadyLoaded('jquery'));

        // This one, load not-existed asset, and should not be loaded.
        $fusionIncludeEngine->load('not-existed');
        $this->assertEquals(false, $fusionIncludeEngine->alreadyLoaded('not-existed'));
    }

    public function testLoadExternalAndAlreadyExists()
    {
        $data = [
            [
                'name'  => 'jquery',
                'files' => [ 'js' => [ 'http://code.jquery.com/ui/1.11.3/jquery-ui.min.js' ], 'css' => [ 'http://code.jquery.com/ui/1.11.3/jquery-ui.min.css' ] ]
            ]
        ];

        $fusionIncludeEngine = new FusionIncludeEngine($data);

        // Here, every asset should not be loaded
        $this->assertEquals(false, $fusionIncludeEngine->alreadyLoaded('jquery'));
        $this->assertEquals(false, $fusionIncludeEngine->alreadyLoaded('appended-asset'));

        // Load first asset, and shoud be loaded only one asset.
        $fusionIncludeEngine->load('jquery');
        $this->assertEquals(true, $fusionIncludeEngine->alreadyLoaded('jquery'));
        $this->assertEquals(false, $fusionIncludeEngine->alreadyLoaded('appended-asset'));

        // Load external asset, and shoud be existed.
        $fusionIncludeEngine->load([
            'name' => 'appended-asset',
            'files' => []
        ]);
        $this->assertEquals(true, $fusionIncludeEngine->alreadyLoaded('jquery'));
        $this->assertEquals(true, $fusionIncludeEngine->alreadyLoaded('appended-asset'));
    }

    public function testAppendSimpleAndGet()
    {
        $shouldBe = '<link rel="stylesheet" type="text/css" href="/some/file.css" />'."\n".'<script src="/some/file.js"></script>';
        $fusionIncludeEngine = new FusionIncludeEngine;

        $this->assertEquals("\n", $fusionIncludeEngine->all());

        $fusionIncludeEngine->load([
            'name' => 'appended-asset',
            'files' => [
                'js' => [ '/some/file.js' ],
                'css' => [ '/some/file.css' ]
            ]
        ]);

        $fusionIncludeEngine->load('appended-asset');
        $this->assertEquals($shouldBe, $fusionIncludeEngine->all());
    }

    public function testLoadWithDefaultRevision()
    {
        $data = [
            [
                'name'  => 'jquery',
                'files' => [ 'js' => [ 'http://code.jquery.com/ui/1.11.3/jquery-ui.min.js' ], 'css' => [ 'http://code.jquery.com/ui/1.11.3/jquery-ui.min.css' ] ]
            ]
        ];

        $shouldBe = '<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.11.3/jquery-ui.min.css?rev=1" />'."\n".'<script src="http://code.jquery.com/ui/1.11.3/jquery-ui.min.js?rev=1"></script>';
        $fusionIncludeEngine = new FusionIncludeEngine($data, 1);
        $fusionIncludeEngine->load('jquery');

        $this->assertEquals($shouldBe, $fusionIncludeEngine->all());
    }

    public function testLoadWithDefinedRevision()
    {
        $data = [
            [
                'name'  => 'jquery',
                'revision' => 11,
                'files' => [ 'js' => [ 'http://code.jquery.com/ui/1.11.3/jquery-ui.min.js' ], 'css' => [ 'http://code.jquery.com/ui/1.11.3/jquery-ui.min.css' ] ]
            ]
        ];

        $shouldBe = '<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.11.3/jquery-ui.min.css?rev=11" />'."\n".'<script src="http://code.jquery.com/ui/1.11.3/jquery-ui.min.js?rev=11"></script>';
        $fusionIncludeEngine = new FusionIncludeEngine($data, 1);
        $fusionIncludeEngine->load('jquery');

        $this->assertEquals($shouldBe, $fusionIncludeEngine->all());
    }

    public function testLoadWithGroupsAndGetFromGroup()
    {
        $data = [
            [
                'name'  => 'jquery',
                'files' => [ 'js' => [ 'http://code.jquery.com/ui/1.11.3/jquery-ui.min.js' ], 'css' => [ 'http://code.jquery.com/ui/1.11.3/jquery-ui.min.css' ] ]
            ],
            [
                'name'  => 'one',
                'group' => 'first',
                'files' => [ 'js' => [ 'one/file.js' ], 'css' => [ 'one/file.css' ] ]
            ],
            [
                'name'  => 'two',
                'group' => 'first',
                'files' => [ 'js' => [ 'two/file.js' ], 'css' => [ 'two/file.css' ] ]
            ],
            [
                'name'  => 'three',
                'group' => 'second',
                'files' => [ 'js' => [ 'three/file.js' ], 'css' => [ 'three/file.css' ] ]
            ]
        ];

        $fusionIncludeEngine = new FusionIncludeEngine($data, null, 'default-group');
        $fusionIncludeEngine->load('jquery')->load('one')->load('two')->load('three');

        $this->assertEquals('<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.11.3/jquery-ui.min.css" />'."\n".'<script src="http://code.jquery.com/ui/1.11.3/jquery-ui.min.js"></script>', $fusionIncludeEngine->all('default-group'));
        $this->assertEquals('<link rel="stylesheet" type="text/css" href="one/file.css" />'."\n".'<link rel="stylesheet" type="text/css" href="two/file.css" />'."\n".'<script src="one/file.js"></script>'."\n".'<script src="two/file.js"></script>', $fusionIncludeEngine->all('first'));
        $this->assertEquals('<link rel="stylesheet" type="text/css" href="three/file.css" />'."\n".'<script src="three/file.js"></script>', $fusionIncludeEngine->all('second'));
        $this->assertEquals("\n", $fusionIncludeEngine->all('third'));
    }

    /**
     * @dataProvider providerInitArrayEight
     */
    public function testRegisterNamespaces($data)
    {
        $fusionIncludeEngine = new FusionIncludeEngine($data);
        $fusionIncludeEngine->registerNamespace('{NS1}', '/namespace');

        $fusionIncludeEngine->load('one')->load('two')->load('three');

        $this->assertEquals('<link rel="stylesheet" type="text/css" href="/file.css" />'."\n".'<script src="/namespace/file.js"></script>', $fusionIncludeEngine->all('first'));
        $this->assertEquals('<link rel="stylesheet" type="text/css" href="/namespace/file.css" />'."\n".'<script src="/file.js"></script>', $fusionIncludeEngine->all('second'));
        $this->assertEquals('<link rel="stylesheet" type="text/css" href="{NS2}/file.css" />'."\n".'<script src="{NS2}/file.js"></script>', $fusionIncludeEngine->all('third'));
    }
    
    /**
     * @dataProvider providerInitArrayEight
     */
    public function testUnregisterNamespaces($data)
    {
        $fusionIncludeEngine = new FusionIncludeEngine($data);
        $fusionIncludeEngine->registerNamespace('{NS1}', '/namespace');

        $fusionIncludeEngine->load('one');

        $fusionIncludeEngine->unregisterNamespace('{NS1}');

        $fusionIncludeEngine->load('two');

        $this->assertEquals('<link rel="stylesheet" type="text/css" href="/file.css" />'."\n".'<script src="/namespace/file.js"></script>', $fusionIncludeEngine->all('first'));
        $this->assertEquals('<link rel="stylesheet" type="text/css" href="{NS1}/file.css" />'."\n".'<script src="/file.js"></script>', $fusionIncludeEngine->all('second'));
    }

    public static function providerInitArrayEight()
    {
        return [
            [
                [
                    [
                        'name'  => 'one',
                        'group' => 'first',
                        'files' => [ 'js' => [ '{NS1}/file.js' ], 'css' => [ '/file.css' ] ]
                    ],
                    [
                        'name'  => 'two',
                        'group' => 'second',
                        'files' => [ 'js' => [ '/file.js' ], 'css' => [ '{NS1}/file.css' ] ]
                    ],
                    [
                        'name'  => 'three',
                        'group' => 'third',
                        'files' => [ 'js' => [ '{NS2}/file.js' ], 'css' => [ '{NS2}/file.css' ] ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * @dataProvider providerInitArrayNine
     */
    public function testAssetsOrder($data)
    {
        $fusionIncludeEngine = new FusionIncludeEngine($data);
        $fusionIncludeEngine->load('three')->load('two')->load('four');

        $this->assertEquals('<link rel="stylesheet" type="text/css" href="/one.css" />'
                      ."\n".'<link rel="stylesheet" type="text/css" href="/three.css" />'
                      ."\n".'<link rel="stylesheet" type="text/css" href="/two.css" />'
                      ."\n".'<link rel="stylesheet" type="text/css" href="/four.css" />'
                      ."\n".'<link rel="stylesheet" type="text/css" href="/five.css" />'
                      ."\n".'', $fusionIncludeEngine->all());
    }

    public static function providerInitArrayNine()
    {
        return [
            [
                [
                    [
                        'name'  => 'two',
                        'order' => 0,
                        'files' => [ 'css' => [ '/two.css' ] ],
                        'require' => [ 'one', 'five' ]
                    ],
                    [
                        'name'  => 'one',
                        'order' => -10,
                        'files' => [ 'css' => [ '/one.css' ] ]
                    ],
                    [
                        'name'  => 'three',
                        'files' => [ 'css' => [ '/three.css' ] ]
                    ],
                    [
                        'name'  => 'five',
                        'files' => [ 'css' => [ '/five.css' ] ],
                        'order' => 10
                    ],
                    [
                        'order' => 1,
                        'name'  => 'four',
                        'files' => [ 'css' => [ '/four.css' ] ]
                    ]
                ]
            ]
        ];
    }
}
