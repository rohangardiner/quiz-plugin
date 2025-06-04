<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://ncoa.com.au/
 * @since             1.0.0
 * @package           Career_Quiz
 *
 * @wordpress-plugin
 * Plugin Name:       Career Quiz
 * Plugin URI:        https://https://ncoa.com.au/
 * Description:       Short quiz to find a user's ideal course.
 * Version:           1.1.1
 * Author:            Rohan
 * Author URI:        https://https://ncoa.com.au//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       career-quiz
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
   die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('CAREER_QUIZ_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-career-quiz-activator.php
 */
function activate_career_quiz() {
   require_once plugin_dir_path(__FILE__) . 'includes/class-career-quiz-activator.php';
   Career_Quiz_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-career-quiz-deactivator.php
 */
function deactivate_career_quiz() {
   require_once plugin_dir_path(__FILE__) . 'includes/class-career-quiz-deactivator.php';
   Career_Quiz_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_career_quiz');
register_deactivation_hook(__FILE__, 'deactivate_career_quiz');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-career-quiz.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_career_quiz() {

   $plugin = new Career_Quiz();
   $plugin->run();

   add_shortcode('career-quiz', 'accsc_career_quiz');
   function accsc_career_quiz() {
      // Check if form was submitted already
      if (isset($_POST['career_quiz_submitted'])) {
         return show_results($_POST); // Pass $_POST or relevant data
      }

      // Get number of questions
      $num_questions_option = get_option('career_quiz_number');
      $num_questions = isset($num_questions_option['career_quiz_field_questions']) ? esc_attr($num_questions_option['career_quiz_field_questions']) : 5; // Default to 5
      $quiz_questions = get_option('career_quiz_questions', array());
      $quizHTML = '';

      // Pick some random questions from the available pool
      $total_questions = count($quiz_questions);
      if ($total_questions > $num_questions) {
         $random_keys = array_rand($quiz_questions, $num_questions);
         // array_rand returns a single key if $num_questions == 1
         $random_keys = (array)$random_keys;
         $selected_questions = [];
         foreach ($random_keys as $key) {
            $selected_questions[$key] = $quiz_questions[$key];
         }
      } else {
         $selected_questions = $quiz_questions;
      }

      // Create form
      $quizHTML .= '<form class="career-quiz" method="post" action="">';

      // Ask each question
      foreach ($selected_questions as $index => $question) {
         $questionHTML = '';
         $questionHTML .= '<div class="quiz-question">';
         $questionHTML .= '<p class="question-title">' . esc_html($question['question']) . '</p>';
         foreach ($question['answers'] as $option) {
            $questionHTML .= '<input 
           type="radio" 
           class="question-option" 
           name="q' . esc_attr($index) . '" 
           value="' . esc_attr($option['text']) . '">' . esc_html($option['text']) . '<br>';
         }
         $questionHTML .= '</div>';
         $quizHTML .= $questionHTML;
      }

      // Close form
      $quizHTML .= '<input type="hidden" name="career_quiz_submitted" value="1">';
      $quizHTML .= '<input type="submit" value="Get your results">';
      $quizHTML .= '</form>';

      // on click submit
      // get results from answer weights

      return $quizHTML;
   }

   function show_results($post_data) {
      // Get questions and outcomes from options to match up scores
      $quiz_questions = get_option('career_quiz_questions', array());
      $quiz_outcomes = get_option('career_quiz_outcomes', array());

      // Initialise Result page HTML to return
      $resultHTML = '';

      // Create array and set initial weightings to 0
      $weightings = array();
      foreach ($quiz_outcomes as $outcome) {
         $weightings[$outcome['name']] = 0.0;
      }

      foreach ($quiz_questions as $index => $question) {
         // The name attribute for each question's radio is "q{$index}"
         $answer_text = isset($post_data["q$index"]) ? $post_data["q$index"] : null;
         $selected_answer = null;

         // Find the selected answer in the question's answers
         if ($answer_text && isset($question['answers'])) {
            foreach ($question['answers'] as $answer) {
               if ($answer['text'] === $answer_text) {
                  $selected_answer = $answer;
                  break;
               }
            }
         }

         // Add selected answer weighting to slected outcome in array
         if ($selected_answer) {
            $weightings[$selected_answer['outcome']] += $selected_answer['weighting'];
         }
      }

      // Get the name of the outcome with the highest total weight
      $winning_outcome = array_search(max($weightings), $weightings);
      // Search outcomes array for outcome with matching name, get that index. Defaults to 1 if not found.
      $index = array_search($winning_outcome, array_column($quiz_outcomes, 'name')) ?? 1;

      // Build and return your results HTML
      $resultHTML .= '<div class="quiz-results">';
         $resultHTML .= '<img src="' . $quiz_outcomes[$index]['image'] . '" class="result-image">';
         $resultHTML .= '<div class="result-content">';
            $resultHTML .= '<h2 class="result-title">' . $quiz_outcomes[$index]['name'] . '</h2>';
            $resultHTML .= '<p class="result-text">' . $quiz_outcomes[$index]['description'] . '</p>';
            $resultHTML .= '<div class="result-links">';
               $resultHTML .= '<a href="' . $quiz_outcomes[$index]['link'] . '" class="quizbutton">Browse Courses</a>';
               $resultHTML .= '<a href="' . esc_url(add_query_arg('retry_quiz', '1')) . '">Back to Quiz</a>';
            $resultHTML .= '</div>';
         $resultHTML .= '</div>';
      $resultHTML .= '</div>';

      // Clear quiz_submitted flag, allowing another submission if the user wants
      unset($_POST['career_quiz_submitted']);
      return $resultHTML;
   }

   // Check for plugin updates
   add_action('init', 'github_plugin_updater_career_quiz');
   function github_plugin_updater_career_quiz() {
      require_once plugin_dir_path(__FILE__) . 'includes/class-career-quiz-updater.php';
      define('WP_GITHUB_FORCE_UPDATE', true);
      if (is_admin()) {
         $config = array(
            'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
            'proper_folder_name' => 'quiz-plugin', // this is the name of the folder your plugin lives in
            'api_url' => 'https://api.github.com/repos/rohangardiner/quiz-plugin', // the GitHub API url of your GitHub repo
            'raw_url' => 'https://raw.github.com/rohangardiner/quiz-plugin/main', // the GitHub raw url of your GitHub repo
            'github_url' => 'https://github.com/rohangardiner/quiz-plugin', // the GitHub url of your GitHub repo
            'zip_url' => 'https://github.com/rohangardiner/quiz-plugin/zipball/main', // the zip url of the GitHub repo
            'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
            'requires' => '6.0', // which version of WordPress does your plugin require?
            'tested' => '6.8.1', // which version of WordPress is your plugin tested up to?
            'readme' => 'README.md', // which file to use as the readme for the version number
            'access_token' => '', // Access private repositories by authorizing under Plugins > GitHub Updates when this example plugin is installed
         );
         new WP_GitHub_Updater_Career_Quiz($config);
      }
   }
}
run_career_quiz();
