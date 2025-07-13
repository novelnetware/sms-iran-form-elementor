<?php
namespace PayamentorIran\Elementor\Fields;

use ElementorPro\Plugin;
use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Fields\Field_Base;

class Elementor_Iran_Mobile_Phone_Field extends Field_Base {

    public function get_type() {
        return 'local-tel';
    }

    public function get_name() {
        return esc_html__('شماره همراه ایران', 'sms-iran-form-elementor');
    }

    public function render($item, $item_index, $form) {
        $form->add_render_attribute(
            'input' . $item_index,
            [
                'class' => 'elementor-field-textual iran-mobile-field',
                'type' => 'tel',
                'inputmode' => 'numeric',
                'pattern' => '^(?:98|\+98|0098|0)?9[0-9]{9}$',
                'title' => !empty($item['local-tel-title']) ? $item['local-tel-title'] : esc_html__('فرمت شماره وارد شده صحیح نیست.', 'sms-iran-form-elementor'),
                'placeholder' => !empty($item['local-tel-placeholder']) ? $item['local-tel-placeholder'] : '09xxxxxxxxx',
                'data-pattern-error' => !empty($item['local-tel-title']) ? $item['local-tel-title'] : esc_html__('فرمت شماره وارد شده صحیح نیست.', 'sms-iran-form-elementor'),
            ]
        );

        echo '<input ' . $form->get_render_attribute_string('input' . $item_index) . '>';
    }

    public function validation($field, $record, $ajax_handler) {
        if (empty($field['value'])) {
            return;
        }

        if (preg_match('/^(?:98|\+98|0098|0)?9[0-9]{9}$/', $field['value']) !== 1) {
            $ajax_handler->add_error(
                $field['id'],
                !empty($field['local-tel-title']) ? $field['local-tel-title'] : esc_html__('فرمت شماره وارد شده صحیح نیست.', 'sms-iran-form-elementor')
            );
        }
    }

    public function update_controls($widget) {
        $elementor = \ElementorPro\Plugin::elementor();

        $control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');

        if (is_wp_error($control_data)) {
            return;
        }

        $field_controls = [
            'local-tel-placeholder' => [
                'name' => 'local-tel-placeholder',
                'label' => esc_html__('برچسب', 'sms-iran-form-elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => '09xxxxxxxxx',
                'dynamic' => ['active' => true],
                'condition' => ['field_type' => $this->get_type()],
                'tab' => 'content', 'label_block' => true,
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'local-tel-title' => [
                'name' => 'local-tel-title',
                'label' => esc_html__('پیغام خطا', 'sms-iran-form-elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => esc_html__('فرمت شماره وارد شده صحیح نیست.', 'sms-iran-form-elementor'),
                'dynamic' => ['active' => true],
                'condition' => ['field_type' => $this->get_type()],
                'tab' => 'content', 'label_block' => true,
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
            'local-tel-direction' => [
                'name' => 'local-tel-direction',
                'label' => esc_html__('جهت متن', 'sms-iran-form-elementor'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'rtl' => [
                        'title' => esc_html__('راست به چپ', 'sms-iran-form-elementor'),
                        'icon' => 'eicon-h-align-right',
                    ],
                    'ltr' => [
                        'title' => esc_html__('چپ به راست', 'sms-iran-form-elementor'),
                        'icon' => 'eicon-h-align-left',
                    ],
                ],
                'default' => 'rtl',
                'selectors' => [
                    '{{WRAPPER}} .iran-mobile-field' => 'direction: {{VALUE}};',
                ],
                'condition' => ['field_type' => $this->get_type()],
                'tab' => 'advanced',
                'inner_tab' => 'form_fields_advanced_tab',
                'tabs_wrapper' => 'form_fields_tabs',
            ],
        ];

        $control_data['fields'] = $this->inject_field_controls($control_data['fields'], $field_controls);
        $widget->update_control('form_fields', $control_data);
    }

    public function __construct() {
        parent::__construct();
        add_action('elementor/preview/init', [$this, 'editor_preview_footer']);
    }

    public function editor_preview_footer() {
        add_action('wp_footer', [$this, 'content_template_script']);
    }

    public function content_template_script() {
        ?>
        <script>
        jQuery(document).ready(() => {
            elementor.hooks.addFilter(
                'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
                function (inputField, item, i) {
                    const fieldId = `form_field_${i}`;
                    const fieldClass = `elementor-field-textual elementor-field iran-mobile-field ${item.css_classes}`;
                    const fieldType = 'tel';
                    const inputmode = 'numeric';
                    const pattern = '^(?:98|\+98|0098|0)?9[0-9]{9}$';
                    const title = item['local-tel-title'] || '<?php echo esc_js(__('فرمت شماره وارد شده صحیح نیست.', 'sms-iran-form-elementor')); ?>';
                    const placeholder = item['local-tel-placeholder'] || '09xxxxxxxxx';
                    
                    return `
                            <input id="${fieldId}" class="${fieldClass}" type="${fieldType}" 
                                   inputmode="${inputmode}" pattern="${pattern}" 
                                   title="${title}" placeholder="${placeholder}"
                                   data-pattern-error="${title}">
                    `;
                }, 10, 3
            );
        });
        </script>
        <?php
    }
}