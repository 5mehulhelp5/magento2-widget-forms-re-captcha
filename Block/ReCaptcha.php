<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
declare(strict_types=1);

namespace Alekseon\WidgetFormsReCaptcha\Block;

use Alekseon\WidgetFormsReCaptcha\Model\Attribute\Source\ReCaptchaType;
use Alekseon\WidgetFormsReCaptcha\Model\CaptchaConfigProvider;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\ReCaptchaUi\Model\IsCaptchaEnabledInterface;

/**
 * Class ReCaptcha
 * @package Alekseon\WidgetFormsReCaptcha\Block
 */
class ReCaptcha extends \Magento\ReCaptchaUi\Block\ReCaptcha implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'Magento_ReCaptchaFrontendUi::recaptcha.phtml';
    /**
     * @var
     */
    private $widgetForm;
    /**
     * @var \Alekseon\WidgetFormsReCaptcha\Model\UiConfigResolver
     */
    private $captchaUiConfigResolver;
    /**
     * @var CaptchaConfigProvider
     */
    private $captchaConfigProvider;

    /**
     * ReCaptcha constructor.
     * @param Template\Context $context
     * @param \Alekseon\WidgetFormsReCaptcha\Model\UiConfigResolver $captchaUiConfigResolver
     * @param IsCaptchaEnabledInterface $isCaptchaEnabled
     * @param Json $serializer
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Alekseon\WidgetFormsReCaptcha\Model\UiConfigResolver $captchaUiConfigResolver,
        IsCaptchaEnabledInterface $isCaptchaEnabled,
        Json $serializer,
        CaptchaConfigProvider $captchaConfigProvider,
        array $data = []
    )
    {
        $this->captchaUiConfigResolver = $captchaUiConfigResolver;
        $this->captchaConfigProvider = $captchaConfigProvider;
        parent::__construct($context, $captchaUiConfigResolver, $isCaptchaEnabled, $serializer, $data);
    }

    /**
     * @param $widgetForm
     * @return $this
     */
    public function setWidgetForm($widgetForm)
    {
        $this->widgetForm = $widgetForm;
        return $this;
    }

    /**
     * @return string | bool
     */
    private function getRecaptchaType()
    {
        return $this->widgetForm ? $this->widgetForm->getRecaptchaType() : false;
    }

    /**
     * @return bool
     */
    public function isReCaptchaEnabled()
    {
        if ($this->getRecaptchaType()) {

            if ($this->getRecaptchaType() == ReCaptchaType::MAGENTO_CAPTCHA_VALUE) {
                return $this->captchaConfigProvider->isRequired();
            }

            return true;
        }
        return false;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getCaptchaUiConfig(): array
    {
        $uiConfig = [];
        if ($this->getRecaptchaType() !== ReCaptchaType::MAGENTO_CAPTCHA_VALUE) {
            $uiConfig = $this->captchaUiConfigResolver->getByType($this->getRecaptchaType());
        }
        return $uiConfig;
    }

    /**
     *
     */
    public function getJsLayout()
    {
        $components = [];

        if ($this->getRecaptchaType() == ReCaptchaType::MAGENTO_CAPTCHA_VALUE) {
            $components['recaptcha'] = [
                'component' => 'Alekseon_WidgetFormsReCaptcha/js/view/widgetFormCaptcha',
                'formId' => 'alekseon_widget_form_' . $this->widgetForm->getId(),
                'configSource' => 'alekseon_widget_form',
                'alekseon_widget_form' => [
                    'captcha' => $this->captchaConfigProvider->getConfig(),
                ],
            ];
        } else {
            $components['recaptcha'] = [
                'component' => 'Magento_ReCaptchaFrontendUi/js/reCaptcha'
            ];
        }

        $this->jsLayout = [
            'components' => $components,
        ];

        return parent::getJsLayout();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     */
    public function toHtml()
    {
        if (!$this->isReCaptchaEnabled()) {
            return '';
        }

        return \Magento\Framework\View\Element\Template::toHtml();
    }
}
