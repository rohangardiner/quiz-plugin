<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://ncoa.com.au/
 * @since      1.0.0
 *
 * @package    Career_Quiz
 * @subpackage Career_Quiz/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Career_Quiz
 * @subpackage Career_Quiz/admin
 * @author     Rohan <rgardiner@actac.com.au>
 */
class Career_Quiz_Admin {

   /**
    * The ID of this plugin.
    *
    * @since    1.0.0
    * @access   private
    * @var      string    $plugin_name    The ID of this plugin.
    */
   private $plugin_name;

   /**
    * The version of this plugin.
    *
    * @since    1.0.0
    * @access   private
    * @var      string    $version    The current version of this plugin.
    */
   private $version;

   /**
    * Initialize the class and set its properties.
    *
    * @since    1.0.0
    * @param      string    $plugin_name       The name of this plugin.
    * @param      string    $version    The version of this plugin.
    */
   public function __construct($plugin_name, $version) {

      $this->plugin_name = $plugin_name;
      $this->version = $version;
   }

   /**
    * Register the stylesheets for the admin area.
    *
    * @since    1.0.0
    */
   public function enqueue_styles() {

      /**
       * This function is provided for demonstration purposes only.
       *
       * An instance of this class should be passed to the run() function
       * defined in Career_Quiz_Loader as all of the hooks are defined
       * in that particular class.
       *
       * The Career_Quiz_Loader will then create the relationship
       * between the defined hooks and the functions defined in this
       * class.
       */

      wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/career-quiz-admin.css', array(), $this->version, 'all');
   }

   /**
    * Register the JavaScript for the admin area.
    *
    * @since    1.0.0
    */
   public function enqueue_scripts() {

      /**
       * This function is provided for demonstration purposes only.
       *
       * An instance of this class should be passed to the run() function
       * defined in Career_Quiz_Loader as all of the hooks are defined
       * in that particular class.
       *
       * The Career_Quiz_Loader will then create the relationship
       * between the defined hooks and the functions defined in this
       * class.
       */

      wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/career-quiz-admin.js', array('jquery'), $this->version, false);
   }
}

// Plugin Options page
function career_quiz_settings_init() {
   // Register a new setting for "career_quiz" page.
   register_setting('career_quiz', 'career_quiz_number');
   register_setting('career_quiz', 'career_quiz_questions');
   register_setting('career_quiz', 'career_quiz_outcomes');

   // Register a new section in the "career_quiz" page.
   add_settings_section(
      'career_quiz_section_questions',
      __('Quiz Settings', 'career_quiz'),
      'career_quiz_section_questions_callback',
      'career_quiz'
   );

   add_settings_section(
      'career_quiz_section_outcomes',
      __('Career Outcomes', 'career_quiz'),
      'career_quiz_section_outcomes_callback',
      'career_quiz'
   );

   add_settings_field(
      'career_quiz_field_number',
      __('Number of questions', 'career_quiz'),
      'career_quiz_field_number_cb',
      'career_quiz',
      'career_quiz_section_questions',
      array(
      'label_for'         => 'career_quiz_field_questions',
      'class'             => 'career_quiz_row',
      'career_quiz_custom_data' => 'custom',
   )
   );

   // Add each setting field to the "career_quiz_section_questions" section on the admin page
   add_settings_field(
      'career_quiz_field_questions',
      __('Quiz Questions', 'career_quiz'),
      'career_quiz_field_questions_cb',
      'career_quiz',
      'career_quiz_section_questions'
   );

   add_settings_field(
      'career_quiz_field_outcomes',
      __('Edit Career Outcomes', 'career_quiz'),
      'career_quiz_field_outcomes_cb',
      'career_quiz',
      'career_quiz_section_outcomes'
   );
}

/**
 * Register our career_quiz_settings_init to the admin_init action hook.
 */
add_action('admin_init', 'career_quiz_settings_init');


/**
 * Custom option and settings:
 *  - callback functions
 */


/**
 * questions section callback function.
 *
 * @param array $args  The settings array, defining title, id, callback.
 */
function career_quiz_section_questions_callback($args) {
?>
   <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Configure questions for Career Quiz', 'career_quiz'); ?></p>
<?php
}

function career_quiz_section_outcomes_callback($args) {
?>
   <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Possible Career Outcomes', 'career_quiz'); ?></p>
<?php
}

/**
 * field callback functions.
 *
 * WordPress has magic interaction with the following keys: label_for, class.
 * - the "label_for" key value is used for the "for" attribute of the <label>.
 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
 * Note: you can add custom key value pairs to be used inside your callbacks.
 *
 * @param array $args
 */

function career_quiz_field_number_cb($args) {
   $option = get_option('career_quiz_number');
   $value = isset($option[$args['label_for']]) ? esc_attr($option[$args['label_for']]) : 5; // Default to 5 questions if not set
?>
   <input
      type="number"
      id="<?php echo esc_attr($args['label_for']); ?>"
      name="career_quiz_number[<?php echo esc_attr($args['label_for']); ?>]"
      value="<?php echo $value; ?>"
      min="0"
      step="1"
      placeholder="Number of questions" />
   <p class="description">
      <?php esc_html_e('Enter the number of questions a user should be asked. Picks randomly from the list below.', 'career_quiz'); ?>
   </p>
<?php
}

function career_quiz_field_questions_cb($args) {
    // Get the saved questions and outcomes from the database
    $questions = get_option('career_quiz_questions', []);
    $outcomes = get_option('career_quiz_outcomes', []);

    // Ensure both are arrays
    if (!is_array($questions)) {
        $questions = [];
    }
    if (!is_array($outcomes)) {
        $outcomes = [];
    }

    ?>
    <div id="career-quiz-questions-container">
        <?php foreach ($questions as $index => $question): ?>
            <div class="career-quiz-question-group" data-index="<?php echo $index; ?>">
                <label>
                    <?php esc_html_e('Question:', 'career_quiz'); ?><br>
                    <input type="text" class="question-text" name="career_quiz_questions[<?php echo $index; ?>][question]" value="<?php echo esc_attr($question['question']); ?>" />
                </label>
                <fieldset>
                    <legend><?php esc_html_e('Possible Answers:', 'career_quiz'); ?></legend>
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div class="career-quiz-answer-group">
                            <label>
                                <?php esc_html_e("Answer $i:", 'career_quiz'); ?>
                                <input type="text" name="career_quiz_questions[<?php echo $index; ?>][answers][<?php echo $i; ?>][text]" value="<?php echo esc_attr($question['answers'][$i]['text'] ?? ''); ?>" />
                            </label>
                            <label>
                                <?php esc_html_e('Outcome:', 'career_quiz'); ?>
                                <select name="career_quiz_questions[<?php echo $index; ?>][answers][<?php echo $i; ?>][outcome]">
                                    <option value=""><?php esc_html_e('Select an Outcome', 'career_quiz'); ?></option>
                                    <?php foreach ($outcomes as $outcome): ?>
                                        <?php $name = isset($outcome['name']) ? esc_html($outcome['name']) : ''; ?>
                                        <option value="<?php echo esc_attr($name); ?>" <?php selected($question['answers'][$i]['outcome'] ?? '', $name); ?>>
                                            <?php echo $name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>
                                <?php esc_html_e('Weighting:', 'career_quiz'); ?>
                                <input type="number" step="0.1" name="career_quiz_questions[<?php echo $index; ?>][answers][<?php echo $i; ?>][weighting]" value="<?php echo esc_attr($question['answers'][$i]['weighting'] ?? ''); ?>" />
                            </label>
                        </div>
                    <?php endfor; ?>
                </fieldset>
                <button type="button" class="remove-question"><?php esc_html_e('Remove', 'career_quiz'); ?></button>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" id="add-question"><?php esc_html_e('Add Question', 'career_quiz'); ?></button>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('career-quiz-questions-container');
            const addButton = document.getElementById('add-question');

            addButton.addEventListener('click', function () {
                const index = container.children.length;
                const group = document.createElement('div');
                group.className = 'career-quiz-question-group';
                group.dataset.index = index;

                group.innerHTML = `
                    <label>
                        <?php esc_html_e('Question:', 'career_quiz'); ?>
                        <input type="text" name="career_quiz_questions[${index}][question]" />
                    </label>
                    <fieldset>
                        <legend><?php esc_html_e('Possible Answers:', 'career_quiz'); ?></legend>
                        ${[1, 2, 3, 4].map(i => `
                            <div class="career-quiz-answer-group">
                                <label>
                                    <?php esc_html_e('Answer', 'career_quiz'); ?> ${i}:
                                    <input type="text" name="career_quiz_questions[${index}][answers][${i}][text]" />
                                </label>
                                <label>
                                    <?php esc_html_e('Outcome:', 'career_quiz'); ?>
                                    <select name="career_quiz_questions[${index}][answers][${i}][outcome]">
                                        <option value=""><?php esc_html_e('Select an Outcome', 'career_quiz'); ?></option>
                                        <?php foreach ($outcomes as $outcome): ?>
                                            <?php $name = isset($outcome['name']) ? esc_html($outcome['name']) : ''; ?>
                                            <option value="<?php echo esc_attr($name); ?>"><?php echo $name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>
                                    <?php esc_html_e('Weighting:', 'career_quiz'); ?>
                                    <input type="number" step="0.1" name="career_quiz_questions[${index}][answers][${i}][weighting]" />
                                </label>
                            </div>
                        `).join('')}
                    </fieldset>
                    <button type="button" class="remove-question"><?php esc_html_e('Remove', 'career_quiz'); ?></button>
                `;

                container.appendChild(group);

                // Add event listener for the remove button
                group.querySelector('.remove-question').addEventListener('click', function () {
                    group.remove();
                });
            });

            // Add event listeners for existing remove buttons
            container.querySelectorAll('.remove-question').forEach(function (button) {
                button.addEventListener('click', function () {
                    button.closest('.career-quiz-question-group').remove();
                });
            });
        });
    </script>
<?php
}

function career_quiz_field_outcomes_cb($args) {
    // Get the saved outcomes from the database
    $outcomes = get_option('career_quiz_outcomes', []);

    // Ensure it's an array
    if (!is_array($outcomes)) {
        $outcomes = [];
    }
?>
    <div id="career-quiz-outcomes-container">
        <?php foreach ($outcomes as $index => $outcome): ?>
            <div class="career-quiz-outcome-group" data-index="<?php echo $index; ?>">
                <label>
                    <?php esc_html_e('Name:', 'career_quiz'); ?><br>
                    <input type="text" name="career_quiz_outcomes[<?php echo $index; ?>][name]" value="<?php echo esc_attr($outcome['name'] ?? ''); ?>" />
                </label>
                <label>
                    <?php esc_html_e('Description:', 'career_quiz'); ?><br>
                    <textarea name="career_quiz_outcomes[<?php echo $index; ?>][description]"><?php echo esc_textarea($outcome['description'] ?? ''); ?></textarea>
                </label>
                <label>
                    <?php esc_html_e('Link:', 'career_quiz'); ?><br>
                    <input type="url" name="career_quiz_outcomes[<?php echo $index; ?>][link]" value="<?php echo esc_attr($outcome['link'] ?? ''); ?>" />
                </label>
                <label>
                    <?php esc_html_e('Image:', 'career_quiz'); ?><br>
                    <input type="url" name="career_quiz_outcomes[<?php echo $index; ?>][image]" value="<?php echo esc_attr($outcome['image'] ?? ''); ?>" />
                </label>
                <button type="button" class="remove-outcome"><?php esc_html_e('Remove', 'career_quiz'); ?></button>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" id="add-outcome"><?php esc_html_e('Add New Outcome', 'career_quiz'); ?></button>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('career-quiz-outcomes-container');
            const addButton = document.getElementById('add-outcome');

            addButton.addEventListener('click', function () {
                const index = container.children.length;
                const group = document.createElement('div');
                group.className = 'career-quiz-outcome-group';
                group.dataset.index = index;

                group.innerHTML = `
                    <label>
                        <?php esc_html_e('Name:', 'career_quiz'); ?>
                        <input type="text" name="career_quiz_outcomes[${index}][name]" />
                    </label>
                    <label>
                        <?php esc_html_e('Description:', 'career_quiz'); ?>
                        <textarea name="career_quiz_outcomes[${index}][description]"></textarea>
                    </label>
                    <label>
                        <?php esc_html_e('Link:', 'career_quiz'); ?>
                        <input type="url" name="career_quiz_outcomes[${index}][link]" />
                    </label>
                    <button type="button" class="remove-outcome"><?php esc_html_e('Remove Outcome', 'career_quiz'); ?></button>
                `;

                container.appendChild(group);

                // Add event listener for the remove button
                group.querySelector('.remove-outcome').addEventListener('click', function () {
                    group.remove();
                });
            });

            // Add event listeners for existing remove buttons
            container.querySelectorAll('.remove-outcome').forEach(function (button) {
                button.addEventListener('click', function () {
                    button.closest('.career-quiz-outcome-group').remove();
                });
            });
        });
    </script>
<?php
}

/**
 * Add the top level menu page.
 */
function career_quiz_options_page() {
   // Add a subpage under Settings which shows the content of: career_quiz_options_page_html()
   add_options_page(
      __('Career Quiz Settings', 'career_quiz'),
      __('Career Quiz Settings', 'career_quiz'),
      'manage_options',
      'career_quiz',
      'career_quiz_options_page_html'
   );
}


/**
 * Register our career_quiz_options_page to the admin_menu action hook.
 */
add_action('admin_menu', 'career_quiz_options_page');


/**
 * Top level menu callback function
 */
function career_quiz_options_page_html() {
   // check user capabilities
   if (! current_user_can('manage_options')) {
      return;
   }

   // add error/update messages

   // check if the user have submitted the settings
   // WordPress will add the "settings-updated" $_GET parameter to the url
   if (isset($_GET['settings-updated'])) {
      // add settings saved message with the class of "updated"
      add_settings_error('career_quiz_messages', 'career_quiz_message', __('Settings Saved', 'career_quiz'), 'updated');
   }

   // show error/update messages
   settings_errors('career_quiz_messages');
?>
   <div class="wrap">
      <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
      <form action="options.php" method="post">
         <?php
         // output security fields for the registered setting "career_quiz"
         settings_fields('career_quiz');
         // output setting sections and their fields
         // (sections are registered for "career_quiz", each field is registered to a specific section)
         do_settings_sections('career_quiz');
         // output save settings button
         submit_button('Save Settings');
         ?>
      </form>
   </div>
<?php
}

function career_quiz_outcomes_dropdown($selected_value = '') {
    // Get the saved outcomes from the database
    $outcomes = get_option('career_quiz_outcomes', []);

    // Ensure it's an array
    if (!is_array($outcomes)) {
        $outcomes = [];
    }

    // Start the dropdown
    echo '<select name="career_quiz_selected_outcome">';
    echo '<option value="">' . esc_html__('Select an Outcome', 'career_quiz') . '</option>';

    // Loop through the outcomes and add each 'name' as an option
    foreach ($outcomes as $index => $outcome) {
        $name = isset($outcome['name']) ? esc_html($outcome['name']) : '';
        $selected = ($selected_value === $name) ? 'selected' : '';
        echo '<option value="' . esc_attr($name) . '" ' . $selected . '>' . $name . '</option>';
    }

    // End the dropdown
    echo '</select>';
}