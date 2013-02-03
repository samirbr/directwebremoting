<?php

  namespace Sawar\DWR;
  
  public class DirectWebRemoting
  {
    public static function map($namespace, $file)
    {
      if (class_exists($class = $namespace . '\\'  . $file->getBasename('.php'))) {
        $reflector = new \Zend_Reflection_Class($class);
        
        if ($reflector->getDocBlock()->hasTag('Sawar\\DWR\\Annotation\\ClassProxy') !== null) {
          return array(
            'constructor' => array(
              'args' => array_map(function ($param) {
                return $param->getName();
              }, $reflector-getMethod('__construct')->getParameters())
            ),
            'methods' => array_filter(function ($method) {
              if ( ! $method->isConstructor() && $method->hasDocBlock('Sawar\\DWR\\Annotation\\MethodProxy')) { 
                return array(
                  'name' => $method->name,
                  'args' => array_map(function ($param) {
                    return $param->getName();
                  }, $reflector->getParameters())
                );
              }
            }, $reflector-getMethods(ReflectionMethod::IS_PUBLIC)),
            'static_methods' => array_filter(function ($method) {
              if ($method->getDocBlock()->hasTag('Sawar\\DWR\\Annotation\\MethodProxy')) { 
                return array(
                  'name' => $method->name,
                  'args' => array_map(function ($param) {
                    return $param->getName();
                  }, $reflector->getParameters())
                );
              }
            }, $reflector-getMethods(ReflectionMethod::IS_STATIC ^ ReflectionMethod::IS_PUBLIC)),
            'properties' => array_filter(function ($property) {
              if ($method->getDocBlock()->hasTag('Sawar\\DWR\\Annotation\\PropertyProxy')) { 
                return $property->name;
              }
            }, $reflector-getProperties()),
            'namespace' => $namespace,
            'type' => 'class',
            'class' => $reflection->getShortName()
          );
        }
        
        return NULL;
      } else {
        $reflector = new \Zend_Reflection_File($file->getRealPath());
        
        return array(
          'functions' => array_filter(function ($function) {
            if ($function->getDocBlock()->hasTag('Sawar\\DWR\\Annotation\\FunctionProxy')) {
              return array(
                'name' => $function->name,
                'args' =>  array_map(function ($param) {
                    return $param->getName();
                }, $reflector->getParameters())
              );
            }
           }, $reflector->getFunctions()), 
          'namespace' => $namespace,
          'type' => 'function',
          'class' => 'functions'
        ));
      }
      
      return NULL;
    }
    
    public static function call($namespace, $class, $method, $params = array(), $is_static = false)
    {
      if (empty($class)) {
        return $namespace . '\\' . $method;
      } else {
        if ($is_static) {
          return $namespace . '\\' . $class . '::' . $method;
        } else {
          return array(new {$namespace . '\\' . $class}, $method);
        }
      }
    
      return call_user_func_array($callable, $params);
    }
  }