<?php

  namespace Sawar\DWR\Provider;

  use Silex\Application;
  use Silex\ServiceProviderInterface;
  use Sawar\DWR\DirectWebRemoting;

  class DirectWebRemotingServiceProvider implements ServiceProviderInterface
  {
    public function register(Application $app)
    {
        $app['dwr'] = $app->protect(function ($name) use ($app, $this) {
          $app['dwr.route'] = $app['dwr.route'] ? $app['dwr.route'] : '/dwr/engine.js';
          $app['dwr.service'] = $app['dwr.service'] ? $app['dwr.service'] : '/dwr';
          
          $app->get($app['dwr.route'], function () use ($app) {
            return $app['twig']->render('Sawar\\DWR\\Resources\\views\\engine.js.twig', array(
              'url' => $app['dwr.service']
            ));
          });
            
          foreach ($app['dwr.namespaces'] as $namespace) {
            $path = $dir['dwr.autoloader']->getNamespaces();            
            $files = $finder->files()->in($path[$namespace] . $namespace)->name('*.php');
          
            foreach ($files as $file) {
              if ($map = DWR::map($file)) {               
                $app->get("/dwr/$namespace/" . $map->class . ".js", function () use ($app, $map) {
                  return $app['twig']->render('Sawar\\DWR\\Resources\\views\\' . $map->type  . '.js.twig', $map);
                });
              }
            }
          }
          
          $app->post($app['dwr.service'], function (Request $request) use ($app) {
            return $app->json(DirectWebRemoting::remote(
              $request->get('ns'),
              $request->get('class', ''),
              $request->get('method'),
              $request->get('params'),
              $request->get('is_static', 'false') === 'true'
            ));
          });
          
          return $app['dwr'];
        });
    }
    
    public function boot(Application $app)
    {
      
    }
  }
  

    