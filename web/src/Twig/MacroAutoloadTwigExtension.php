<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Environment;
use Twig\TwigFunction;
use Twig\Error\Error as ErrorError;

class MacroAutoloadTwigExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return array(
            // "*"" is used to get "template_macro" as $macro as third argument
            new TwigFunction('macro_*', array($this, 'twig_render_macro'), array(
                'needs_environment' => true, // $env first argument will render the macro
                'needs_context' => true,     // $context second argument an array of view vars
                'is_safe' => array('html'),  // function returns escaped html
                'is_variadic' => true,       // and takes any number of arguments
            ))
        );
    }

    public function twig_render_macro(Environment $env, array $context, $macro, array $vars = array())
    {
        $func = $macro;
        $name = $macro;

        $notInContext = 0; // helps generate a unique context key

        $varToContextKey = function ($var) use (&$context, $name, $func, &$notInContext) {
            if (false !== $idx = array_search($var, $context, true)) {
                return $idx;
            }

            // else the var does not belong to context
            $key = '_'.$func.'_'.++$notInContext;
            $context[$key] = $var;

            return $key;
        };

        $args = implode(', ', array_map($varToContextKey, $vars));

        $twig = <<<EOT
{% import 'global/macros.html.twig' as gm %}
{{ gm.$func($args) }}
EOT;
        try {
            $html = $env->createTemplate($twig)->render($context);
        } catch (ErrorError $e) {
            throw $e;
        }

        return $html;
    }

    public function getName()
    {
        return 'macro_autoload_extension';
    }
}
