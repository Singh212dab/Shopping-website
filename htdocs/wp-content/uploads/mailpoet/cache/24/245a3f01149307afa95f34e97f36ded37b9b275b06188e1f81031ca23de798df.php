<?php

use MailPoetVendor\Twig\Environment;
use MailPoetVendor\Twig\Error\LoaderError;
use MailPoetVendor\Twig\Error\RuntimeError;
use MailPoetVendor\Twig\Extension\SandboxExtension;
use MailPoetVendor\Twig\Markup;
use MailPoetVendor\Twig\Sandbox\SecurityError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedTagError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedFilterError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedFunctionError;
use MailPoetVendor\Twig\Source;
use MailPoetVendor\Twig\Template;

/* woocommerce/settings_button.html */
class __TwigTemplate_ff4e5c787cae0f85041f50adead5ca5d5e255bf141146ab2e8d3fbdde89b6389 extends \MailPoetVendor\Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<script type=\"text/javascript\">
  jQuery(function(\$){
    \$('#mailpoet_woocommerce_customize_button')
      .insertAfter(\$('#email_notification_settings-description'))
      .show();
  });
</script>

<p id=\"mailpoet_woocommerce_customize_button\" style=\"display: none;\">
  <a class=\"button button-primary\"
    href=\"?page=mailpoet-newsletter-editor&id=";
        // line 11
        echo \MailPoetVendor\twig_escape_filter($this->env, ($context["woocommerce_template_id"] ?? null), "html", null, true);
        echo "\"
    data-automation-id=\"mailpoet_woocommerce_customize_button\"
  >
    ";
        // line 14
        echo $this->extensions['MailPoet\Twig\I18n']->translateWithContext("Customize with MailPoet", "Button in WooCommerce settings page");
        echo "
  </a>
</p>
";
    }

    public function getTemplateName()
    {
        return "woocommerce/settings_button.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  55 => 14,  49 => 11,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "woocommerce/settings_button.html", "C:\\Bitnami\\wordpress-5.7.2-0\\apps\\wordpress\\htdocs\\wp-content\\plugins\\mailpoet\\views\\woocommerce\\settings_button.html");
    }
}
