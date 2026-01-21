<?php
defined('ABSPATH') || exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

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
    /* =====================================================
     * FORM SELECTION
     * ===================================================== */
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
     *                     FORM STYLES
     * ===================================================== */

    $this->start_controls_section(
      'section_form_style',
      [
        'label' => __('Form', 'dynamic-form'),
        'tab'   => Controls_Manager::TAB_STYLE,
      ]
    );

    /* ---------- Background ---------- */
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

    /* ---------- Border Color ---------- */
    $this->add_control(
      'form_border_color',
      [
        'label' => __('Border Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form' =>
          '--df-form-border: 1px solid {{VALUE}};',
        ],
      ]
    );

    /* ---------- Border Style ---------- */
    $this->add_control(
      'form_border_type',
      [
        'label' => __('Border Style', 'dynamic-form'),
        'type'  => Controls_Manager::SELECT,
        'options' => [
          'solid'  => __('Solid', 'dynamic-form'),
          'dashed' => __('Dashed', 'dynamic-form'),
          'dotted' => __('Dotted', 'dynamic-form'),
          'double' => __('Double', 'dynamic-form'),
          'none'   => __('None', 'dynamic-form'),
        ],
        'default' => 'solid',
        'selectors' => [
          '{{WRAPPER}} .df-form ' =>
          '--df-form-border-type: {{VALUE}};',
        ],
      ]
    );

    /* ---------- Border Width ---------- */
    $this->add_control(
      'form_border_width',
      [
        'label' => __('Border Width', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'range' => [
          'px' => ['min' => 1, 'max' => 10],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-form ' =>
          '--df-form-border-width: {{SIZE}}px;',
        ],
      ]
    );

    /* ---------- Border Radius ---------- */
    $this->add_control(
      'form_radius',
      [
        'label' => __('Border Radius', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'size_units' => ['px', '%'],
        'selectors' => [
          '{{WRAPPER}} .df-form' =>
          '--df-form-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    /* ---------- Padding ---------- */
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

    /* ---------- Margin ---------- */
    $this->add_responsive_control(
      'form_margin',
      [
        'label' => __('Margin', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-form' =>
          '--df-form-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    /* ---------- Field Gap ---------- */
    $this->add_control(
      'field_gap',
      [
        'label' => __('Field Gap', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'size_units' => ['px', 'em'],
        'range' => [
          'px' => ['min' => 0, 'max' => 60],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-form' =>
          '--df-field-gap: {{SIZE}}{{UNIT}};',
        ],
      ]
    );

    /* ---------- Max Width ---------- */
    $this->add_control(
      'form_max_width',
      [
        'label' => __('Max Width', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'size_units' => ['px', '%'],
        'range' => [
          'px' => ['min' => 300, 'max' => 1600],
          '%'  => ['min' => 20, 'max' => 100],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-form' =>
          '--df-form-max-width: {{SIZE}}{{UNIT}};',
        ],
      ]
    );
    /* Shadow Color */
    $this->add_control(
      'form_shadow_color',
      [
        'label' => __('Shadow Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form' =>
          '--df-form-shadow-color: {{VALUE}};',
        ],
      ]
    );

    /* Shadow Offset X */
    $this->add_control(
      'form_shadow_x',
      [
        'label' => __('Shadow Horizontal', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'range' => [
          'px' => ['min' => -50, 'max' => 50],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-form' =>
          '--df-form-shadow-x: {{SIZE}}px;',
        ],
      ]
    );

    /* Shadow Offset Y */
    $this->add_control(
      'form_shadow_y',
      [
        'label' => __('Shadow Vertical', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'range' => [
          'px' => ['min' => -50, 'max' => 50],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-form' =>
          '--df-form-shadow-y: {{SIZE}}px;',
        ],
      ]
    );

    /* Shadow Blur */
    $this->add_control(
      'form_shadow_blur',
      [
        'label' => __('Shadow Blur', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'range' => [
          'px' => ['min' => 0, 'max' => 80],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-form' =>
          '--df-form-shadow-blur: {{SIZE}}px;',
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
          '{{WRAPPER}} .df-form .df-field' => '--df-field-bg: {{VALUE}};',
        ],
      ]
    );


    $this->add_control(
      'field_radius',
      [
        'label' => __('Field Radius', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-field' =>
          '--df-field-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    /* ---------- Field Padding ---------- */
    $this->add_responsive_control(
      'field_padding',
      [
        'label' => __('Padding', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-field' =>
          '--df-field-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    /* ---------- Field margin ---------- */
    $this->add_responsive_control(
      'field_margin',
      [
        'label' => __('Margin', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-field' =>
          '--df-field-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    /* Shadow Color */
    $this->add_control(
      'field_shadow_color',
      [
        'label' => __('Shadow Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-field' =>
          '--df-field-shadow-color: {{VALUE}};',
        ],
      ]
    );

    /* Shadow Offset X */
    $this->add_control(
      'field_shadow_x',
      [
        'label' => __('Shadow Horizontal', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'range' => [
          'px' => ['min' => -50, 'max' => 50],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-form .df-field' =>
          '--df-field-shadow-x: {{SIZE}}px;',
        ],
      ]
    );

    /* Shadow Offset Y */
    $this->add_control(
      'field_shadow_y',
      [
        'label' => __('Shadow Vertical', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'range' => [
          'px' => ['min' => -50, 'max' => 50],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-form .df-field' =>
          '--df-field-shadow-y: {{SIZE}}px;',
        ],
      ]
    );

    /* Shadow Blur */
    $this->add_control(
      'field_shadow_blur',
      [
        'label' => __('Shadow Blur', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'range' => [
          'px' => ['min' => 0, 'max' => 80],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-form .df-field' =>
          '--df-field-shadow-blur: {{SIZE}}px;',
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

    /* ---------- Border Radius ---------- */
    $this->add_control(
      'input_radius',
      [
        'label' => __('Border Radius', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'size_units' => ['px', '%'],
        'selectors' => [
          '{{WRAPPER}} .df-form .df-input' =>
          '--df-input-border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    $this->add_control(
      'input_padding',
      [
        'label' => __('Padding', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'size_units' => ['px', '%'],
        'selectors' => [
          '{{WRAPPER}} .df-form .df-input' =>
          '--df-input-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    $this->add_control(
      'input_margin',
      [
        'label' => __('Margin', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'size_units' => ['px', '%'],
        'selectors' => [
          '{{WRAPPER}} .df-form .df-input' =>
          '--df-input-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    $this->add_control(
      'input_color',
      [
        'label' => __('Font Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-input ' => '--df-input-color: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'input_bg',
      [
        'label' => __('Background', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-input ' => '--df-input-bg: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'input_border_color',
      [
        'label' => __('Border Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-input ' => '--df-input-border-color: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'input_focus_color',
      [
        'label' => __('Focus Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-input ' => '--df-input-focus-border: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'input_accent_color',
      [
        'label' => __('Accent Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-input ' => '--df-choice-accent: {{VALUE}};',
        ],
      ]
    );


    $this->add_control(
      'input_border_type',
      [
        'label' => __('Border Style', 'dynamic-form'),
        'type'  => Controls_Manager::SELECT,
        'options' => [
          'solid'  => __('Solid', 'dynamic-form'),
          'dashed' => __('Dashed', 'dynamic-form'),
          'dotted' => __('Dotted', 'dynamic-form'),
          'double' => __('Double', 'dynamic-form'),
          'none'   => __('None', 'dynamic-form'),
        ],
        'default' => 'solid',
        'selectors' => [
          '{{WRAPPER}} .df-form .df-input' =>
          '--df-input-border-type: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'input_border_width',
      [
        'label' => __('Border Width', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'range' => [
          'px' => ['min' => 0, 'max' => 20],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-form .df-input' =>
          '--df-input-border-width: {{SIZE}}px;',
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

    /* ---------- Label Color ---------- */
    $this->add_control(
      'label_color',
      [
        'label' => __('Text Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-field label,
            {{WRAPPER}} .df-form .df-field legend' =>
          '--df-label-color: {{VALUE}};',
        ],
      ]
    );

    $this->add_responsive_control(
      'label_padding',
      [
        'label' => __('Padding', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-field label,
            {{WRAPPER}} .df-form .df-field legend' =>
          '--df-label-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    $this->add_responsive_control(
      'label_margin',
      [
        'label' => __('Margin', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-field label,
            {{WRAPPER}} .df-form .df-field legend' =>
          '--df-label-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    /* ---------- Label Typography ---------- */
    $this->add_group_control(
      Group_Control_Typography::get_type(),
      [
        'name'     => 'label_typography',
        'selector' => '{{WRAPPER}} .df-form .df-field label,
                        {{WRAPPER}} .df-form .df-field legend',
      ]
    );

    $this->end_controls_section();


    /* =====================================================
     * SUBHEADING STYLES
     * ===================================================== */
    $this->start_controls_section(
      'section_subheading_style',
      [
        'label' => __('Subheading', 'dynamic-form'),
        'tab'   => Controls_Manager::TAB_STYLE,
      ]
    );

    $this->add_group_control(
      Group_Control_Typography::get_type(),
      [
        'name'     => 'subheading_typography',
        'selector' => '{{WRAPPER}} .df-subheading',
      ]
    );

    $this->add_control(
      'subheading_color',
      [
        'label' => __('Text Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-subheading' => '--df-subheading-color: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'subheading_bg',
      [
        'label' => __('Background', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-subheading' => '--df-subheading-bg: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'subheading_align',
      [
        'label' => __('Alignment', 'dynamic-form'),
        'type'  => Controls_Manager::CHOOSE,
        'options' => [
          'left'   => ['title' => __('Left', 'dynamic-form'), 'icon' => 'eicon-text-align-left'],
          'center' => ['title' => __('Center', 'dynamic-form'), 'icon' => 'eicon-text-align-center'],
          'right'  => ['title' => __('Right', 'dynamic-form'), 'icon' => 'eicon-text-align-right'],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-subheading' => '--df-subheading-align: {{VALUE}};',
        ],
      ]
    );

    $this->add_responsive_control(
      'subheading_padding',
      [
        'label' => __('Padding', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-subheading' =>
          '--df-subheading-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    $this->add_responsive_control(
      'subheading_margin',
      [
        'label' => __('Margin', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-subheading' =>
          '--df-subheading-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    $this->add_group_control(
      Group_Control_Border::get_type(),
      [
        'name'     => 'subheading_border',
        'selector' => '{{WRAPPER}} .df-subheading',
      ]
    );

    $this->add_control(
      'subheading_radius',
      [
        'label' => __('Border Radius', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-subheading' =>
          '--df-subheading-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
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

    /* ---------- Position ---------- */
    $this->add_control(
      'button_position',
      [
        'label' => __('Button Position', 'dynamic-form'),
        'type'  => Controls_Manager::CHOOSE,
        'options' => [
          'flex-start' => [
            'title' => __('Left', 'dynamic-form'),
            'icon'  => 'eicon-text-align-left',
          ],
          'center' => [
            'title' => __('Center', 'dynamic-form'),
            'icon'  => 'eicon-text-align-center',
          ],
          'flex-end' => [
            'title' => __('Right', 'dynamic-form'),
            'icon'  => 'eicon-text-align-right',
          ],
        ],
        'default' => 'center',
        'selectors' => [
          '{{WRAPPER}} .df-form .df-field-submit' => '--df-button-align: {{VALUE}};',
        ],
      ]
    );

    /* ---------- Colors ---------- */
    $this->add_control(
      'button_bg',
      [
        'label' => __('Background', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' => '--df-button-bg: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'button_hover_bg',
      [
        'label' => __('Hover Background', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' => '--df-button-hover-bg: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'button_color',
      [
        'label' => __('Text Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' => '--df-button-color: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'button_hover_color',
      [
        'label' => __('Hover Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' => '--df-button-hover-color: {{VALUE}};',
        ],
      ]
    );

    /* ---------- Size ---------- */
    $this->add_control(
      'button_radius',
      [
        'label' => __('Border Radius', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' =>
          '--df-button-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    $this->add_control(
      'button_width',
      [
        'label' => __('Width', 'dynamic-form'),
        'type' => Controls_Manager::SLIDER,
        'size_units' => ['px', '%'],
        'range' => [
          'px' => ['min' => 100, 'max' => 500],
          '%'  => ['min' => 20, 'max' => 100],
        ],
        'default' => ['size' => 100, 'unit' => '%'],
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' =>
          '--df-button-width: {{SIZE}}{{UNIT}};',
        ],
      ]
    );

    $this->add_responsive_control(
      'button_padding',
      [
        'label' => __('Padding', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' =>
          '--df-button-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    $this->add_responsive_control(
      'button_margin',
      [
        'label' => __('Margin', 'dynamic-form'),
        'type'  => Controls_Manager::DIMENSIONS,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' =>
          '--df-button-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    /* ---------- Typography ---------- */
    $this->add_group_control(
      Group_Control_Typography::get_type(),
      [
        'name'     => 'button_typography',
        'selector' => '{{WRAPPER}} .df-form .df-submit-btn',
      ]
    );

    /* ---------- Shadow ---------- */
    $this->add_control(
      'button_shadow_heading',
      [
        'label' => __('Shadow', 'dynamic-form'),
        'type'  => Controls_Manager::HEADING,
        'separator' => 'before',
      ]
    );

    $this->add_control(
      'button_shadow_color',
      [
        'label' => __('Shadow Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' =>
          '--df-button-shadow-color: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'button_shadow_x',
      [
        'label' => __('Shadow Horizontal', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'range' => [
          'px' => ['min' => -50, 'max' => 50],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' =>
          '--df-button-shadow-x: {{SIZE}}px;',
        ],
      ]
    );

    $this->add_control(
      'button_shadow_y',
      [
        'label' => __('Shadow Vertical', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'range' => [
          'px' => ['min' => -50, 'max' => 50],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' =>
          '--df-button-shadow-y: {{SIZE}}px;',
        ],
      ]
    );

    $this->add_control(
      'button_shadow_blur',
      [
        'label' => __('Shadow Blur', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'range' => [
          'px' => ['min' => 0, 'max' => 80],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' =>
          '--df-button-shadow-blur: {{SIZE}}px;',
        ],
      ]
    );

    $this->add_control(
      'button_border_type',
      [
        'label' => __('Border Style', 'dynamic-form'),
        'type'  => Controls_Manager::SELECT,
        'options' => [
          'solid'  => __('Solid', 'dynamic-form'),
          'dashed' => __('Dashed', 'dynamic-form'),
          'dotted' => __('Dotted', 'dynamic-form'),
          'double' => __('Double', 'dynamic-form'),
          'none'   => __('None', 'dynamic-form'),
        ],
        'default' => 'solid',
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' =>
          '--df-button-border-type: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'button_border_width',
      [
        'label' => __('Border Width', 'dynamic-form'),
        'type'  => Controls_Manager::SLIDER,
        'range' => [
          'px' => ['min' => 1, 'max' => 20],
        ],
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' =>
          '--df-button-border-width: {{SIZE}}px;',
        ],
      ]
    );

    $this->add_control(
      'button_border_color',
      [
        'label' => __('Border Color', 'dynamic-form'),
        'type'  => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .df-form .df-submit-btn' =>
          '--df-button-border-color: {{VALUE}};',
        ],
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

    $form = DF_FormRepository::get($form_id);

    if (empty($form) || !is_array($form)) {
      if (current_user_can('edit_posts')) {
        echo '<p style="color:#d63638;">' .
          esc_html__('Dynamic Form: Selected form does not exist or was deleted.', 'dynamic-form') .
          '</p>';
      }
      return;
    }

    echo DF_Renderer::render($form);
  }
}
