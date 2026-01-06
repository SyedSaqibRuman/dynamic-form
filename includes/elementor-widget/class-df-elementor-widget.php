<?php
defined('ABSPATH') || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class DF_Elementor_Widget extends Widget_Base
{
  /* =====================================================
   * BASIC META
   * ===================================================== */

  public function get_name()
  {
    return 'dynamic_form';
  }

  public function get_title()
  {
    return __('Dynamic Form', 'dynamic-form');
  }

  public function get_icon()
  {
    return 'eicon-form-horizontal';
  }

  public function get_categories()
  {
    return ['general'];
  }

  /* =====================================================
   * CONTROLS
   * ===================================================== */

  protected function register_controls()
  {
    /* -------------------------------
     * Form Selection
     * ------------------------------- */
    $this->start_controls_section(
      'section_form',
      ['label' => __('Form', 'dynamic-form')]
    );

    $this->add_control(
      'form_id',
      [
        'label'   => __('Select Form', 'dynamic-form'),
        'type'    => Controls_Manager::SELECT,
        'options' => DF_FormRepository::get_form_options(),
        'default' => '',
      ]
    );

    $this->end_controls_section();

    /* =====================================================
     * FORM STYLES
     * ===================================================== */
    $this->start_controls_section(
      'section_form_style',
      [
        'label' => __('Form', 'dynamic-form'),
        'tab'   => Controls_Manager::TAB_STYLE,
      ]
    );

    $this->add_control(
      'form_bg',
      [
        'label' => __('Background', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form' => '--df-form-bg: {{VALUE}};',
        ],
      ]
    );

    $this->add_responsive_control(
      'form_padding',
      [
        'label' => __('Padding', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-form' =>
          '--df-form-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    $this->add_control(
      'field_gap',
      [
        'label' => __('Field Gap', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'selectors' => [
          '{{WRAPPER}} .df-form' => '--df-field-gap: {{SIZE}}{{UNIT}};',
        ],
      ]
    );

    $this->end_controls_section();

    /* =====================================================
     * FIELD STYLES
     * ===================================================== */
    $this->start_controls_section(
      'section_field_style',
      [
        'label' => __('Fields', 'dynamic-form'),
        'tab'   => Controls_Manager::TAB_STYLE,
      ]
    );

    $this->add_control(
      'field_bg',
      [
        'label' => __('Field Background', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-field' => '--df-field-bg: {{VALUE}};',
        ],
      ]
    );

    $this->add_responsive_control(
      'field_padding',
      [
        'label' => __('Field Padding', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-field' =>
          '--df-field-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    $this->add_responsive_control(
      'field_margin',
      [
        'label' => __('Field Margin', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-field' =>
          '--df-field-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    $this->end_controls_section();

    /* =====================================================
     * INPUT STYLES
     * ===================================================== */
    $this->start_controls_section(
      'section_input_style',
      [
        'label' => __('Inputs', 'dynamic-form'),
        'tab'   => Controls_Manager::TAB_STYLE,
      ]
    );

    $this->add_control(
      'input_bg',
      [
        'label' => __('Input Background', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-input' => '--df-input-bg: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'input_border_color',
      [
        'label' => __('Border Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-input' => '--df-input-border-color: {{VALUE}};',
        ],
      ]
    );

    $this->add_group_control(
      Group_Control_Typography::get_type(),
      [
        'name'     => 'input_typography',
        'selector' => '{{WRAPPER}} .df-input',
      ]
    );

    $this->end_controls_section();

    /* =====================================================
     * LABEL STYLES
     * ===================================================== */
    $this->start_controls_section(
      'section_label_style',
      [
        'label' => __('Labels', 'dynamic-form'),
        'tab'   => Controls_Manager::TAB_STYLE,
      ]
    );

    $this->add_group_control(
      Group_Control_Typography::get_type(),
      [
        'name'     => 'label_typography',
        'selector' => '{{WRAPPER}} .df-field label, {{WRAPPER}} .df-field legend',
      ]
    );

    $this->end_controls_section();

    /* =====================================================
     * BUTTON STYLES
     * ===================================================== */
    $this->start_controls_section(
      'section_button_style',
      [
        'label' => __('Submit Button', 'dynamic-form'),
        'tab'   => Controls_Manager::TAB_STYLE,
      ]
    );

    $this->add_control(
      'button_bg',
      [
        'label' => __('Background', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-submit-btn' => '--df-button-bg: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'button_color',
      [
        'label' => __('Text Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-submit-btn' => '--df-button-color: {{VALUE}};',
        ],
      ]
    );

    $this->add_group_control(
      Group_Control_Typography::get_type(),
      [
        'name'     => 'button_typography',
        'selector' => '{{WRAPPER}} .df-submit-btn',
      ]
    );

    $this->end_controls_section();
  }

  /* =====================================================
   * RENDER
   * ===================================================== */

  protected function render()
  {
    $settings = $this->get_settings_for_display();
    $form_id  = absint($settings['form_id']);

    if (!$form_id) {
      echo '<p>' . esc_html__('Please select a form.', 'dynamic-form') . '</p>';
      return;
    }

    echo DF_Renderer::render(
      DF_FormRepository::get($form_id)
    );
  }
}
