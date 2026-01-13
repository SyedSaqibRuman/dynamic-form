<?php
defined('ABSPATH') || exit;

class DF_Renderer
{
  /* =====================================================
   * PUBLIC RENDER METHODS
   * ===================================================== */

  public static function render(array $form, array $args = []): string
  {
    $args = wp_parse_args($args, [
      'preview' => false,
    ]);

    $form_id  = absint($form['id']);
    $fields   = json_decode($form['fields'] ?? '[]', true);
    $settings = json_decode($form['settings'] ?? '{}', true);

    if (!is_array($fields)) {
      return '<p>' . esc_html__('Invalid form configuration.', 'dynamic-form') . '</p>';
    }

    ob_start();
?>
    <form
      id="df-form"
      class="df-form <?php echo $args['preview'] ? 'df-preview-form' : ''; ?>"
      <?php echo $args['preview'] ? 'onsubmit="return false"' : 'method="post"'; ?>
      data-form-id="<?php echo esc_attr($form_id); ?>"
      novalidate>

      <?php if (!$args['preview']) : ?>
        <?php wp_nonce_field('df_frontend_submit', '_df_nonce'); ?>
        <input type="hidden" name="df_form_id" value="<?php echo esc_attr($form_id); ?>">
      <?php endif; ?>

      <?php foreach ($fields as $field) : ?>
        <?php self::render_field($field); ?>
      <?php endforeach; ?>

      <!-- Submit Button -->
      <?php if (DF_Settings::get('turnstile_enabled')) : ?>
        <div class="df-form-group df-turnstile">
          <div
            class="cf-turnstile"
            data-sitekey="<?php echo esc_attr(DF_Settings::get('turnstile_site_key')); ?>"
            <?php if (DF_Settings::get('turnstile_mode') === 'invisible') :
            ?>
            data-appearance="interaction-only"
            <?php endif; ?>></div>
        </div>
      <?php endif;
      ?>
      <div
        class="df-form-group df-field df-field-submit"
        data-field-type="submit">
        <button type="submit" class="df-submit-btn df-input">
          <?php echo esc_html($settings['submit_label'] ?? __('Submit', 'dynamic-form')); ?>
        </button>
      </div>

    </form>
  <?php
    return ob_get_clean();
  }

  public static function render_preview(array $form): string
  {
    return self::render($form, ['preview' => true]);
  }

  /* =====================================================
   * INTERNAL HELPERS
   * ===================================================== */

  /**
   * Build CSS variables from JSON styles
   * Example:
   * { "base": { "bg": "#fff", "padding": "12px" } }
   * → --df-field-bg:#fff;--df-field-padding:12px;
   */
  private static function build_css_vars(array $styles): string
  {
    if (empty($styles['base']) || !is_array($styles['base'])) {
      return '';
    }

    $css = '';

    foreach ($styles['base'] as $key => $value) {
      if ($value === '' || $value === null) {
        continue;
      }

      $css .= '--df-field-' . sanitize_key($key) . ':' . esc_attr($value) . ';';
    }

    return $css;
  }

  /* =====================================================
   * FIELD RENDERER
   * ===================================================== */

  private static function render_field(array $field): void
  {
    $type     = $field['fieldType'] ?? 'text';
    $label    = $field['fieldName'] ?? '';
    $required = !empty($field['required']);
    $options  = $field['options'] ?? [];
    $field_id = $field['id'] ?? uniqid('df_');

    $input_id = 'df_' . $field_id;
    $name     = 'df_field_' . $field_id;

    $style_vars = self::build_css_vars($field['styles'] ?? []);
  ?>
    <div
      class="df-form-group df-field df-field-<?php echo esc_attr($type); ?>"
      data-field-id="<?php echo esc_attr($field_id); ?>"
      data-label="<?php echo esc_attr($label); ?>"
      data-field-type="<?php echo esc_attr($type); ?>"
      style="<?php echo esc_attr($style_vars); ?>">

      <?php if (!in_array($type, ['checkbox', 'radio', 'subheading'], true)) : ?>
        <label for="<?php echo esc_attr($input_id); ?>">
          <?php echo esc_html($label); ?>
          <?php if ($required) : ?>
            <span class="df-required">*</span>
          <?php endif; ?>
        </label>
      <?php endif; ?>

      <?php switch ($type):

        case 'text':
        case 'email':
        case 'tel':
        case 'number': ?>
          <input
            class="df-input"
            type="<?php echo esc_attr($type); ?>"
            id="<?php echo esc_attr($input_id); ?>"
            name="<?php echo esc_attr($name); ?>"
            <?php echo $required ? 'required' : ''; ?> />
          <?php break; ?>

        <?php
        case 'textarea': ?>
          <textarea
            class="df-input"
            id="<?php echo esc_attr($input_id); ?>"
            name="<?php echo esc_attr($name); ?>"
            <?php echo $required ? 'required' : ''; ?>></textarea>
          <?php break; ?>

        <?php
        case 'select': ?>
          <select
            class="df-input"
            id="<?php echo esc_attr($input_id); ?>"
            name="<?php echo esc_attr($name); ?>"
            <?php echo $required ? 'required' : ''; ?>>
            <option value=""><?php esc_html_e('Select', 'dynamic-form'); ?></option>
            <?php foreach ((array) $options as $val) : ?>
              <option value="<?php echo esc_attr($val); ?>">
                <?php echo esc_html($val); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php break; ?>

        <?php
        case 'phone': ?>
          <input
            class="df-input"
            type="tel"
            id="<?php echo esc_attr($input_id); ?>"
            name="<?php echo esc_attr($name); ?>"
            inputmode="tel"
            autocomplete="tel"
            <?php echo $required ? 'required' : ''; ?> />
          <?php break; ?>

        <?php
        case 'checkbox': ?>
          <fieldset class="df-options">
            <legend>
              <?php echo esc_html($label); ?>
              <?php if ($required) : ?><span class="df-required">*</span><?php endif; ?>
            </legend>
            <?php foreach ((array) $options as $val) : ?>
              <label>
                <input
                  class="df-input"
                  type="checkbox"
                  name="<?php echo esc_attr($name); ?>[]"
                  value="<?php echo esc_attr($val); ?>">
                <?php echo esc_html($val); ?>
              </label>
            <?php endforeach; ?>
          </fieldset>
          <?php break; ?>

        <?php
        case 'radio': ?>
          <fieldset class="df-options">
            <legend>
              <?php echo esc_html($label); ?>
              <?php if ($required) : ?><span class="df-required">*</span><?php endif; ?>
            </legend>
            <?php foreach ((array) $options as $val) : ?>
              <label>
                <input
                  class="df-input"
                  type="radio"
                  name="<?php echo esc_attr($name); ?>"
                  value="<?php echo esc_attr($val); ?>">
                <?php echo esc_html($val); ?>
              </label>
            <?php endforeach; ?>
          </fieldset>
          <?php break; ?>

        <?php
        case 'subheading': ?>
          <h3 class="df-subheading df-input">
            <?php echo esc_html($label); ?>
          </h3>
          <?php break; ?>

      <?php endswitch; ?>
    </div>
<?php
  }
}
