<?php
/*
 * @Author: Amirhossein Hosseinpour <https://amirhp.com>
 * @Last modified by: amirhp-com <its@amirhp.com>
 * @Last modified time: 2025/01/20 03:21:19
 */
use BlackSwan\Telegram\Notifier;
if (!class_exists("class_setting")) {
  class class_setting extends Notifier {
    public function __construct() {
      parent::__construct();
      add_action("admin_init", array($this, "register_settings"), 1);
      add_action("admin_menu", array($this, "admin_menu"));
    }
    public function admin_menu() {
      add_options_page($this->title, $this->title_small, "manage_options", $this->db_slug, array($this, "setting_page"));
    }
    protected function get_setting_options_list() {
      return array(
        array(
          "name" => "general",
          "data" => array(
            "debug"           => "no",
            "jdate"           => "no",
            "admin_bar_link"  => "yes",
            "gettext_replace" => "",
            "str_replace"     => "",
            "token"           => "",
            "username"        => "",
            "chat_ids"        => "",
            "notifications"   => "",
          ),
        ),
      );
    }
    public function register_settings() {
      foreach ($this->get_setting_options_list() as $sections) {
        foreach ($sections["data"] as $id => $def) {
          add_option("{$this->db_slug}__{$id}", $def, "", 'no');
          register_setting("{$this->db_slug}__{$sections["name"]}", "{$this->db_slug}__{$id}");
        }
      }
    }
    public function setting_page(){
      ob_start();
      $this->update_footer_info();
      wp_enqueue_script('wp-color-picker');
      wp_enqueue_style('wp-color-picker');
      // Enqueue jQuery UI
      wp_enqueue_script('jquery-ui');
      wp_enqueue_script('jquery-ui-core');
      wp_enqueue_script('jquery-ui-datepicker');

      wp_enqueue_style($this->db_slug . "-fas", "//cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css", array(), current_time("timestamp"));
      wp_enqueue_style($this->db_slug . "-setting", "{$this->assets_url}css/backend.css", [], time());
      wp_enqueue_script($this->db_slug . "-popper", "{$this->assets_url}js/popper.min.js", ["jquery"], "1.2.1");
      wp_enqueue_script($this->db_slug . "-tippy", "{$this->assets_url}js/tippy-bundle.umd.js", ["jquery"], "1.2.1");
      wp_enqueue_script($this->db_slug . "-setting", "{$this->assets_url}js/jquery.repeater.min.js", ["jquery"], "1.2.1");
      wp_enqueue_script($this->db_slug . "-panel", "{$this->assets_url}js/panel.js", ["jquery"], time());
      $data = $this->read("notifications");
      $localized = apply_filters("blackswan-telegram/notif-panel/localize-front-script", array(
        "ajax" => admin_url("admin-ajax.php"),
        "action" => $this->td,
        "notif_json" => htmlentities($data),
        "nonce" => wp_create_nonce($this->td),
        "wait" => _x("Please wait ...", "front-js", "blackswan-telegram"),
        "check_toggle" => _x("Turn On (green) to Enable this Notif", "front-js", "blackswan-telegram"),
        "delete_confirm" => _x("Are you sure you want to delete this notifications? If you do and Save this page, YOU CANNOT UNDO WHAT YOU DID.", "front-js", "blackswan-telegram"),
        "delete_all" => _x("Are you sure you want to delete all notifications? If you do and Save this page, YOU CANNOT UNDO WHAT YOU DID.", "front-js", "blackswan-telegram"),
        /* translators: 1: copied text */
        "copied" => _x("Copied %s to clipboard.", "front-js", "blackswan-telegram"),
        "code_copied" => _x("JSON Data Copied to clipboard.", "front-js", "blackswan-telegram"),
        "error_slug_empty" => _x("first select a notif type", "front-js", "blackswan-telegram"),
        "error_option_empty" => _x("no option found for selected notif", "front-js", "blackswan-telegram"),
        "unknown" => _x("An Unknown Error Occured. Check console for more information.", "front-js", "blackswan-telegram"),
        "md" => array(
          "asterisk" => _x('Unmatched asterisk (*) found.', "front-js", "blackswan-telegram"),
          "underscore" => _x('Unmatched underscore (_) found.', "front-js", "blackswan-telegram"),
          "backtick" => _x('Unmatched backtick (`) found.', "front-js", "blackswan-telegram"),
          "triple_backticks" => _x('Unmatched triple backticks (```) found.', "front-js", "blackswan-telegram"),
          "invalid" => _x('Invalid {entity} formatting. Example: {example}', "front-js", "blackswan-telegram"),
          "valid" => _x('Success, Markdown is valid!', "front-js", "blackswan-telegram"),
        ),
      ));
      wp_localize_script($this->db_slug . "-panel", "_panel", $localized);
      is_rtl() and wp_add_inline_style($this->db_slug, "#wpbody-content { font-family: bodyfont, roboto, Tahoma; }");
      ?>
      <div class="wrap">
        <!-- region head and success message -->
        <h1 class="page-title-heading">
          <span class='fas fa-cogs'></span>&nbsp;
          <strong style="font-weight: 700;"><?php echo esc_html($this->title); ?></strong> <sup>v.<?php echo esc_html($this->version); ?></sup>
        </h1>
        <form method="post" action="options.php">
          <!-- region tab items -->
            <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
              <a href="#" data-tab="tab_general" class="nav-tab nav-tab-active"><?php echo  __("General", "blackswan-telegram"); ?></a>
              <a href="#" data-tab="tab_workspace" class="nav-tab"><?php echo  __("Notifications", "blackswan-telegram"); ?></a>
              <a href="#" data-tab="tab_translate" class="nav-tab"><?php echo  __("Translate", "blackswan-telegram"); ?></a>
              <a href="#" data-tab="tab_str_replace" class="nav-tab"><?php echo  __("String Replace", "blackswan-telegram"); ?></a>
              <a href="#" data-tab="tab_documentation" class="nav-tab"><?php echo  __("Documentation", "blackswan-telegram"); ?></a>
            </nav>
          <!-- region tab content -->
            <?php settings_fields("{$this->db_slug}__general"); ?>
            <div class="tab-content tab-active" data-tab="tab_general">
              <br>
              <table class="form-table wp-list-table widefat striped table-view-list posts">
                <thead>
                  <?php
                  echo "<tr class='sync_site_tr border-top'><th colspan=2>
                    <h2 style='display: inline-block; margin:0;'>" . __("General Configuration", "blackswan-telegram") . "</h2></th></tr>";
                  ?>
                </thead>
                <tbody>
                  <?php
                  $this->print_setting_checkbox([
                    "slug" => "debug",
                    "caption" => __("Enable Debug Mode", "blackswan-telegram"),
                    "desc" => __("Enable to log detailed information for troubleshooting. Recommended for development only.", "blackswan-telegram"),
                  ]);
                  $this->print_setting_checkbox([
                    "slug" => "admin_bar_link",
                    "caption" => __("Show Plugin Link in Admin Bar", "blackswan-telegram"),
                    "desc" => __("Enable this option to display a link to the plugin in the WordPress admin bar for administrators.", "blackswan-telegram"),
                ]);
                  $this->print_setting_checkbox([
                    "slug" => "jdate",
                    "caption" => __("Enable Built-in Jalali Date", "blackswan-telegram"),
                    "desc" => __("Enable to use the Jalali (Shamsi) calendar format. Disable other Jalali plugins to avoid conflicts.", "blackswan-telegram"),
                  ]);
                  /* translators: 1: @BotFather hyperlinked, 2: validate token link, 3: line break <br> */
                  $this->print_setting_input([
                    "slug" => "token",
                    "caption" => esc_html__("Your bot token", "blackswan-telegram"),
                    "desc" => sprintf(
                      /* translators: 1: bot father link, 2: connect btn, 3: break */
                      __('Message %1$s on Telegram to register your bot and get the token.%3$sEnter the token, save settings and click “%2$s”.', "blackswan-telegram"),
                      "<a href='https://t.me/botfather' target='_blank'>@BotFather</a>",
                      "<a href='#' class='button button-link connect'>" . __("Connect Webhook", "blackswan-telegram") . "</a>",
                      "<br>",
                    ),
                  ]);
                  /* translators: 1: validate token link, 2: line break <br> */
                  $this->print_setting_input([
                    "slug" => "username",
                    // "extra_html" => "readonly",
                    "caption" => esc_html__("Your bot username", "blackswan-telegram"),
                    "desc" => __('Enter the username provided when you created the bot (without the "@" symbol).', "blackswan-telegram"),
                  ]);
                  $this->print_setting_textarea([
                    "slug" => "chat_ids",
                    "caption" => esc_html__("Default Chat IDs", "blackswan-telegram"),
                    "desc" => sprintf(
                      /* translators: 1: break */
                      __("After saving the bot token, message your bot or add it to a group/channel to get the Chat ID.%sEnter each Chat ID, one per line, in the field above.", "blackswan-telegram"),
                      "<br>",
                    ),
                  ]);
                  ?>
                </tbody>
              </table>
            </div>
            <div class="tab-content" data-tab="tab_workspace">
              <br>
              <table class="form-table wp-list-table widefat striped table-view-list posts workspace-wrapper-parent">
                <thead>
                  <?php
                  echo "<tr class='sync_site_tr border-top'><th colspan=2>
                    <h2 style='display: inline-block; margin: 0;'>" . __("Notifications Panel", "blackswan-telegram") . "</h2></th></tr>";
                  ?>
                  <tr>
                    <th colspan="2"><?php echo $this->render_workspace_tools();?></th>
                  </tr>
                </thead>
                <tbody class="workplace" data-empty="<?php echo esc_attr(__("No notif added to panel, you toolbar above to add your first notif.", "blackswan-telegram"));?>"></tbody>
                <tfoot>
                  <tr class="type-textarea notifications toggle-export-import hide">
                    <th scope="row" colspan="2">
                      <h3 style="margin-top: 0;"><label for="notifications"><?php echo __("Import / Export as JSON Data", "blackswan-telegram");?></label></h3><a href="#" class="button button-secondary copy-code"><?php echo __("Copy to Clipboard", "blackswan-telegram");?></a><br>
                      <p class="data-textarea"><textarea name="blackswan-telegram__notifications" id="notifications" rows="4" style="width: 100%; direction: ltr; font-family: monospace; font-size: smaller;" class="regular-text"><?php echo $this->read("notifications");?></textarea></p>
                      <p class="description"><?php echo __("you can use the json data to migrate settings across multiple sites. Enter JSON Data and Save page to reload Workspace.", "blackswan-telegram");?></p>
                    </th>
                  </tr>
                </tfoot>
              </table>
            </div>
            <div class="tab-content" data-tab="tab_documentation">
              <br>
              <div class="desc">
                <table class="fixed wp-list-table widefat striped table-view-list posts hooks-docs">
                  <thead>
                    <tr>
                      <th><strong><?php echo __("Hook Entry", "blackswan-telegram");?></strong></th>
                      <td><strong><?php echo __("Description", "blackswan-telegram");?></strong></td>
                    </tr>
                  </thead>


                  <tr>
                    <td><?php echo $this->highlight('apply_filters("blackswan-telegram/notif-panel/notif-types-array", $options)');?></td>
                    <td>Add new Notification type, e.g. Support for Custom Plugin</td>
                  </tr>

                  <tr>
                    <td><?php echo $this->highlight('do_action("blackswan-telegram/notif-panel/after-notif-setting", $slug)');?></td>
                    <td>Add Custom Setting a Notification type</td>
                  </tr>

                  <tr>
                    <td><?php echo $this->highlight('apply_filters("blackswan-telegram/notif-panel/notif-macro-list", $array, $slug)');?></td>
                    <td>Add Custom Macro for a Notification type</td>
                  </tr>

                  <tr>
                    <td><?php echo $this->highlight('do_action("blackswan-telegram/notif-panel/after-macro-list", $macros_filtered, $default_macro, $slug)');?></td>
                    <td>Print Custom content after Macro section for a Notification type</td>
                  </tr>

                  <tr>
                    <td><?php echo $this->highlight('apply_filters("blackswan-telegram/notif-panel/notif-default-message", "", $slug)');?></td>
                    <td>Change Default Message for a Notification type</td>
                  </tr>

                  <tr>
                    <td><?php echo $this->highlight('apply_filters("blackswan-telegram/notif-panel/notif-default-parser",false, $slug)');?></td>
                    <td>Change Default Parser as HTML Checkbox state for a Notification type</td>
                  </tr>

                  <tr>
                    <td><?php echo $this->highlight('apply_filters("blackswan-telegram/notif-panel/notif-default-buttons", "", $slug)');?></td>
                    <td>Change Default Buttons list for a Notification type</td>
                  </tr>

                  <tr>
                    <td>Host Cron Job</td>
                    <td>wp-config: <?php echo $this->highlight("define('DISABLE_WP_CRON', true);");?>
                    <br>cronjob: <?php echo $this->highlight("* * * * * wget --delete-after ".home_url("/wp-cron.php?doing_wp_cron")." >/dev/null 2>&1");?>
                    <br>cronjob: <?php echo $this->highlight('/usr/local/bin/php /home/public_html/wp-cron.php?doing_wp_cron >/dev/null 2>&1');?></td>
                  </tr>

                </table>
                <br>
                <table class="wp-list-table widefat striped table-view-list posts">
                  <thead>
                    <tr>
                      <th><strong>Credits</strong></th>
                      <td><strong>Library</strong></td>
                    </tr>
                  </thead>
                  <tr>
                    <td>jquery.repeater</td>
                    <td><a href="https://github.com/DubFriend/jquery.repeater" target="_blank">https://github.com/DubFriend/jquery.repeater</a></td>
                  </tr>
                </table>
              </div>
            </div>
            <div class="tab-content" data-tab="tab_translate">
              <br>
              <div class="desc repeater translation-panel">
                <table class="wp-list-table widefat striped table-view-list posts">
                  <thead>
                    <tr>
                      <th class="th-original"><?php echo  __("Original", "blackswan-telegram"); ?></th>
                      <th class="th-translate"><?php echo  __("Translate", "blackswan-telegram"); ?></th>
                      <th class="th-text-domain"><?php echo  __("TextDomain", "blackswan-telegram"); ?></th>
                      <th class="th-options"><?php echo  __("Options", "blackswan-telegram"); ?></th>
                      <th class="th-action" style="width: 100px;"><?php echo  __("Action", "blackswan-telegram"); ?></th>
                    </tr>
                  </thead>
                  <tbody data-repeater-list="gettext">
                    <tr data-repeater-item style="display:none;">
                      <td class="th-original"><span class="dashicons dashicons-menu-alt move-handle"></span>&nbsp;<input type="text" data-slug="original" name="original" value="" placeholder="<?php echo  __("Original text ...", "blackswan-telegram"); ?>" /></td>
                      <td class="th-translate"><input type="text" data-slug="translate" name="translate" placeholder="<?php echo  __("Translate to ...", "blackswan-telegram"); ?>" /></td>
                      <td class="th-text-domain"><input type="text" data-slug="text_domain" name="text_domain" placeholder="<?php echo  __("Text Domain (Optional)", "blackswan-telegram"); ?>" /></td>
                      <td class="th-options">
                        <label><input type="checkbox" value="yes" data-slug="use_replace" name="use_replace" />&nbsp;<?php echo  __("Partial Replace?", "blackswan-telegram"); ?></label>&nbsp;&nbsp;
                        <!-- <label><input type="checkbox" value="yes" data-slug="use_regex" name="use_regex"/>&nbsp;<?php echo  __("Use RegEx?", "blackswan-telegram"); ?></label>&nbsp;&nbsp; -->
                        <label><input type="checkbox" value="yes" data-slug="two_sided" name="two_sided" />&nbsp;<?php echo  __("Translated Origin?", "blackswan-telegram"); ?></label>
                      </td>
                      <td class="th-action">
                        <a class="button button-secondary" data-repeater-delete><span style="margin: 4px 0;" class="dashicons dashicons-trash"></span><?php echo  __("Delete Row", "blackswan-telegram"); ?></a>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <br>
                <a data-repeater-create class="button button-secondary button-hero"><span style="margin: 12px 8px;" class="dashicons dashicons-insert"></span><?php echo  __("Add New Row", "blackswan-telegram"); ?></a>&nbsp;&nbsp;
              </div>
              <br><br>
              <table class="form-table wp-list-table widefat striped table-view-list posts">
                <thead>
                  <?php
                  echo "<tr class='sync_site_tr border-top'><th colspan=2><strong style='display: inline-block;'>" . __("Migrate Translation", "blackswan-telegram") . "</strong></th></tr>";
                  ?>
                </thead>
                <tbody>
                  <?php
                  $this->print_setting_textarea(["slug" => "gettext_replace", "caption" => __("Translation Data", "blackswan-telegram"), "style" => "width: 100%; direction: ltr; min-height: 300px; font-family: monospace; font-size: 0.8rem;",]);
                  ?>
                </tbody>
              </table>
            </div>
            <div class="tab-content" data-tab="tab_str_replace">
              <br>
              <div class="desc repeater str_replace-panel">
                <table class="wp-list-table widefat striped table-view-list posts">
                  <thead>
                    <tr>
                      <th class="th-original"><?php echo  __("Original", "blackswan-telegram"); ?></th>
                      <th class="th-translate"><?php echo  __("Replace", "blackswan-telegram"); ?></th>
                      <th class="th-options"><?php echo  __("Options", "blackswan-telegram"); ?></th>
                      <th class="th-action" style="width: 100px;"><?php echo  __("Action", "blackswan-telegram"); ?></th>
                    </tr>
                  </thead>
                  <tbody data-repeater-list="gettext">
                    <tr data-repeater-item style="display:none;">
                      <td class="th-original"><span class="dashicons dashicons-menu-alt move-handle"></span>&nbsp;<input type="text" data-slug="original" name="original" value="" placeholder="<?php echo  __("Original text ...", "blackswan-telegram"); ?>" /></td>
                      <td class="th-translate"><input type="text" data-slug="translate" name="translate" placeholder="<?php echo  __("Translate to ...", "blackswan-telegram"); ?>" /></td>
                      <td class="th-options">
                        <label><input type="checkbox" value="yes" data-slug="buffer" name="buffer" />&nbsp;<?php echo  __("Green: Replace in Output Buffer | Red: Replace in Content Only", "blackswan-telegram"); ?></label>&nbsp;&nbsp;
                        <label><input type="checkbox" value="yes" data-slug="active" name="active" />&nbsp;<?php echo  __("Active", "blackswan-telegram"); ?></label>&nbsp;&nbsp;
                      </td>
                      <td class="th-action">
                        <a class="button button-secondary" data-repeater-delete><span style="margin: 4px 0;" class="dashicons dashicons-trash"></span><?php echo  __("Delete Row", "blackswan-telegram"); ?></a>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <br>
                <a data-repeater-create class="button button-secondary button-hero"><span style="margin: 12px 8px;" class="dashicons dashicons-insert"></span><?php echo  __("Add New Row", "blackswan-telegram"); ?></a>&nbsp;&nbsp;
              </div>
              <br><br>
              <table class="form-table wp-list-table widefat striped table-view-list posts">
                <thead>
                  <?php
                  echo "<tr class='sync_site_tr border-top'><th colspan=2><strong style='display: inline-block;'>" . __("Migrate String Replace", "blackswan-telegram") . "</strong></th></tr>";
                  ?>
                </thead>
                <tbody>
                  <?php
                  $this->print_setting_textarea(["slug" => "str_replace", "caption" => __("String Replace Data", "blackswan-telegram"), "style" => "width: 100%; direction: ltr; min-height: 300px; font-family: monospace; font-size: 0.8rem;",]);
                  ?>
                </tbody>
              </table>
            </div>
          <!-- region submit wrapper -->
            <div class="submit_wrapper">
              <button class="button button-primary button-hero"><?php echo  __("Save setting", "blackswan-telegram"); ?></button>
              <a href='#' class="button button-secondary button-hero send_test"><?php echo __("Send Test Message", "blackswan-telegram");?></a>
              <a href='#' class="button button-secondary button-hero connect"><?php echo __("Connect Webhook", "blackswan-telegram");?></a>
              <a href='#' class="button button-secondary button-hero disconnect"><?php echo __("Disconnect Webhook", "blackswan-telegram");?></a>
            </div>
        </form>
      </div>
      <!-- region tab switch script and js-repeater -->
      <?php
      $html = ob_get_contents();
      ob_end_clean();
      print $html;
    }
    public function highlight($php){
      return str_replace("&lt;?php", "", highlight_string('<?php'.$php, 1));
    }
    public function sample_setting_row_wrapper(){
      ob_start();
      ?>
      <tr class="setting-row newly-added" data-type="{slug}">
        <th colspan="2">
          <h3 class="entry-name">{category} / {title}</h3>&nbsp;
          <div class="fa-pull-right">
            <input type="checkbox" class="enable--entry" data-slug="_enabled" data-tippy-content="<?php echo esc_attr_x("Turn On (green) to Enable this Notif", "front-js", "blackswan-telegram");?>" />
            <a href="#" data-tippy-content="<?php echo esc_attr__("Change Notif Configurations", "blackswan-telegram");?>" class="edit--entry"><span class="fa-stack"><i class="fa-solid fa-circle fa-stack-2x"></i><i class="fas fa-stack-1x fa-inverse fa-cog"></i></span></a>
            <a href="#" data-tippy-content="<?php echo esc_attr__("Delete this Notif", "blackswan-telegram");?>" class="delete--entry"><span class="fa-stack"><i class="fa-solid fa-circle fa-stack-2x"></i><i class="fas fa-stack-1x fa-inverse fa-trash-alt"></i></span></a>
          </div>
          <table class="sub-setting form-table wp-list-table widefat striped table-view-list fixed hide"><tbody><tr><th colspan="2">{row_details}</th></tr></tbody></table>
        </th>
      </tr>
      <?php
      $htmloutput = ob_get_contents();
      ob_end_clean();
      return apply_filters("blackswan-telegram/notif-panel/sample_setting_row_wrapper", $htmloutput);
    }
    public function render_workspace_tools(){
      ?>
      <div class="workspace-notifications-list">
        <div class="workspace-tools">
          <select id="notif_types">
            <option value="" ><?php echo __("-- Select Notif --", "blackswan-telegram");?></option>
            <?php
            do_action("blackswan-telegram/notif-panel/after-general-notif-dropdown");
            $this->print_notif_types();
            do_action("blackswan-telegram/notif-panel/after-notif-types-dropdown");
            ?>
          </select>
          <a href="#" class="button button-secondary add-new-notif"><?php echo __("Add New", "blackswan-telegram");?></a>
          <a href="#" class="button button-secondary clear-all-notif"><?php echo __("Clear All", "blackswan-telegram");?></a>
          <a href="#" class="button button-secondary export-import-notif"><?php echo __("Import / Export", "blackswan-telegram");?></a>
        </div>
        <div class="template-wrapper">
          <?php
          foreach ($this->print_notif_types(true) as $category => $items) {
            foreach ($items["options"] as $key => $value) {
              echo "<!-- Notif Setting / ".esc_attr($items["title"])." / ".esc_attr($value)." -->";
              echo "<template id='".esc_attr($key)."'>";
              echo $this->print_notif_setting($key);
              echo "</template>";
            }
          }
          ?>
          <template id="sample_setting_row_wrapper"><?php echo $this->sample_setting_row_wrapper();?></template>
          <?php do_action("blackswan-telegram/notif-panel/notif-types-setting"); ?>
        </div>
      </div>
      <?php
    }
    public function print_notif_types($return=false){
      $options = array();
      $options["wp_users"] = array(
        "title" => __("WordPress Users", "blackswan-telegram"),
        "options" => array(
          "wp_user_registered" => __("New User Registered", "blackswan-telegram"),
          "wp_user_edited" => __("User Profile Updated", "blackswan-telegram"),
        ),
      );
      if (class_exists("WooCommerce")) {
        $options["woocommerce_order"] = array(
          "title" => __("WooCommerce / Order", "blackswan-telegram"),
          "options" => array(
            "wc_new_order"              => __("New Order Created", "blackswan-telegram"),
            "wc_order_saved"            => __("Order Updated / Saved", "blackswan-telegram"),
            "wc_trash_order"            => __("Trash Order", "blackswan-telegram"),
            "wc_delete_order"           => __("Delete Order", "blackswan-telegram"),
            "wc_order_refunded"         => __("Refunded Order", "blackswan-telegram"),
            "wc_payment_complete"       => __("Order Payment Complete", "blackswan-telegram"),
            "wc_checkout_processed"     => __("Checkout Order Processed", "blackswan-telegram"),
            "wc_checkout_api_processed" => __("Checkout Order Processed via API", "blackswan-telegram"),
          ),
        );
        $statuses = wc_get_order_statuses();
        $emails = wp_list_pluck(WC()->mailer()->get_emails(), "title", "id");
        $statuses_options = $mail_options = array();
        $statuses_options["wc_order_status_changed"] = __("Changed to anything", "blackswan-telegram");
        foreach ($statuses as $slug => $name) {
          $slug = $this->remove_status_prefix($slug);
          /* translators: 1: status name */
          $statuses_options["wc_order_status_to_{$slug}"] = sprintf(__("Changed to %s", "blackswan-telegram"), $name);
        }
        $options["woocommerce_order_status"] = array(
          "title" => __("WooCommerce / Order / Status", "blackswan-telegram"),
          "options" => $statuses_options,
        );
        $options["woocommerce_product"] = array(
          "title" => __("WooCommerce / Product", "blackswan-telegram"),
          "options" => array(
            "wc_product_updated" => __("Product Updated or Saved", "blackswan-telegram"),
            "--wc_product_purchased" => __("Product Purchased on New Order", "blackswan-telegram"),
            "--wc_product_stock_increased" => __("Product Stock Quantity Increased", "blackswan-telegram"),
            "--wc_product_stock_decreased" => __("Product Stock Quantity Decreased", "blackswan-telegram"),
          ),
        );
        $mail_options["wc_mail_sent"] = sprintf(__("Mail Sent: All Emails", "blackswan-telegram"), $name);
        foreach ($emails as $slug => $name) {
          /* translators: 1: email name */
          $mail_options["wc_mail_{$slug}"] = sprintf(__("Mail Sent: %s", "blackswan-telegram"), $name);
        }
        $options["woocommerce_mail"] = array(
          "title" => __("WooCommerce / E-Mail", "blackswan-telegram"),
          "options" => $mail_options,
        );
      }
      $options = apply_filters("blackswan-telegram/notif-panel/notif-types-array", $options);
      if ($return) return $options;
      foreach ($options as $category => $items) {
        echo "<optgroup data-id='".esc_attr($category)."' label='".esc_attr($items["title"])."'></optgroup>";
        foreach ($items["options"] as $key => $value) {
          $attr = "";
          if ($this->str_starts_with($key, "--")) {
            $attr .= "disabled ";
          }
          echo "<option ".esc_attr($attr)." value='".esc_attr($key)."'>".esc_attr($value)."</option>";
        }
      }
    }
    public function print_notif_setting($slug){
      ob_start();
      $btn_placeholder = "Button label | Button URL\nButton label | Button URL";
      do_action("blackswan-telegram/notif-panel/before-notif-setting", $slug);
      $default_message = apply_filters("blackswan-telegram/notif-panel/notif-default-message", "", $slug);
      $default_parser = apply_filters("blackswan-telegram/notif-panel/notif-default-parser", false, $slug);
      $default_buttons = apply_filters("blackswan-telegram/notif-panel/notif-default-buttons", "", $slug);
      ?>
      <tr class="tg-message">
        <th><?php echo __("Message", "blackswan-telegram");?></th>
        <td>
          <textarea rows="4" data-slug="message"><?php echo $default_message;?></textarea>
          <p class="description"><?php
          echo sprintf(
            /* translators: 1: help link, 2: markdown validator */
            __('You can use <b>HTML</b>, <b>Markdown</b>, and <b>Macros</b> in your message (See the %1$s). Ensure your Markdown is valid with the "%2$s".', "blackswan-telegram"),
            '<a href="https://core.telegram.org/bots/api#markdown-style" target="_blank">'.__("Telegram Formatting Guide", "blackswan-telegram").'</a>',
            "<a href='#' class='button button-link validate_markdown'>".__("Markdown Validator", "blackswan-telegram")."</a>");?></p>
        </td>
      </tr>
      <tr class="tg-formatting">
        <th><?php echo __("HTML Formatting", "blackswan-telegram");?></th>
        <td>
          <label><input type="checkbox" <?php echo checked($default_parser, true, false);?> data-slug="html_parser" value="html">&nbsp;
          <?php echo __("Enable (green) to use HTML, or disable (red) to use Markdown", "blackswan-telegram");?></label>
        </td>
      </tr>
      <tr class="description">
        <td colspan="2" class="macro-list">
          <?php
           echo "<h4 style='color: #1d2327;margin: 0;font-weight: normal;'>" . __("Available Macros for this Notif", "blackswan-telegram") . "</h4>";
           ?>
           <div class="macro-list-wrapper">
            <?php
              $default_macros = array(
                "general" => array(
                  "title" => __("General", "blackswan-telegram"),
                  "macros" => array(
                    "current_time"       => _x("Current Time","macro", "blackswan-telegram"),
                    "current_date"       => _x("Current Date","macro", "blackswan-telegram"),
                    "current_date_time"  => _x("Current Date-time","macro", "blackswan-telegram"),
                    "current_jdate"      => _x("Current Jalali Date","macro", "blackswan-telegram"),
                    "current_jdate_time" => _x("Current Jalali Date-time","macro", "blackswan-telegram"),
                    "current_user_id"    => _x("Current User id","macro", "blackswan-telegram"),
                    "current_user_name"  => _x("Current User name","macro", "blackswan-telegram"),
                    "site_name"          => _x("Site name","macro", "blackswan-telegram"),
                    "site_url"           => _x("Site URL","macro", "blackswan-telegram"),
                    "admin_url"          => _x("Admin URL","macro", "blackswan-telegram"),
                  ),
                ),
              );
              $filtered_hooks = apply_filters("blackswan-telegram/notif-panel/notif-macro-list", (array) $default_macros, $slug);
              foreach ((array) $filtered_hooks as $macro_slug => $macro_category) {
                echo "<strong>".(isset($macro_category['title'])?$macro_category['title']:$macro_slug)."</strong>";
                if (isset($macro_category["macros"])) {
                  foreach ($macro_category["macros"] as $key => $value) {
                    echo "<copy data-tippy-content='".esc_attr($value)."'>{{$key}}</copy>";
                  }
                }
              }
              ?>
              <?php do_action("blackswan-telegram/notif-panel/after-macro-list", $filtered_hooks, $default_macros, $slug); ?>
           </div>
        </td>
      </tr>
      <tr class="tg-buttons">
        <th><?php echo __("Buttons", "blackswan-telegram");?></th>
        <td>
          <textarea rows="4" data-slug="btn_row1" placeholder="<?php echo $btn_placeholder;?>"><?php echo $default_buttons;?></textarea>
          <p class="description">
            <?php echo __("You can add up to four buttons. Both labels and URLs can use plain text or macros, and labels can also include emojis. You can use @page_slug, #page_id or {special_pages} supported by PeproDev Ultimate Profile Solutions as button URL too.", "blackswan-telegram");?>
            <br><?php
             echo sprintf(
               /* translators: 1: sample btn */
              __("Sample Button: %s", "blackswan-telegram"),
              '<copy data-tippy-content="'.esc_attr__("Sample Button", "blackswan-telegram").'">🌏 Visit {site_name}|{site_url}</copy>');?>
          </p>
        </td>
      </tr>
      <?php
      do_action("blackswan-telegram/notif-panel/after-notif-setting", $slug);
      $htmloutput = ob_get_contents();
      ob_end_clean();
      return apply_filters("blackswan-telegram/notif-panel/notif-setting-html", $htmloutput, $slug);
    }
    protected function update_footer_info() {
      add_filter("update_footer", function () {
        /* translators: 1: plugin name, 2: version ID */
        return sprintf(_x('%1$s — Version %2$s', "footer-copyright", "blackswan-telegram"), $this->title, $this->version);
      }, 999999999);
    }
    #region settings-option functions >>>>>>>>>>>>>
    protected function print_setting_input($data) {
      extract(wp_parse_args($data, array(
        "slug"        => "",
        "caption"     => "",
        "type"        => "text",
        "desc"        => "",
        "extra_html"  => "",
        "extra_class" => "",
      )));
      echo "<tr class='type-" . esc_attr($type) . " $extra_class " . sanitize_title($slug) . "'>
              <th scope='row'><label for='$slug'>$caption</label></th>
              <td><input
                      name='" . esc_attr("{$this->db_slug}__{$slug}") . "'
                      id='" . esc_attr($slug) . "'
                      type='" . esc_attr($type) . "'
                      placeholder='" . esc_attr($caption) . "'
                      data-tippy-content='" . esc_attr(
                        /* translators: 1: field name */
                        sprintf(_x("Enter   %s", "setting-page", "blackswan-telegram"), $caption)
                      ) . "' value='" . esc_attr((get_option("{$this->db_slug}__{$slug}", "") ?: "")) . "'
                      class='regular-text " . esc_attr($extra_class) . "' " . esc_attr($extra_html) . " />
              <p class='description'>" . ($desc) . "</p></td></tr>";
    }
    protected function print_setting_checkbox($data) {
      extract(wp_parse_args($data, array(
        "slug"        => "",
        "caption"     => "",
        "desc"        => "",
        "value"       => "yes",
        "extra_html"  => "",
        "extra_class" => "",
      )));
      echo "<tr class='type-checkbox $extra_class " . sanitize_title($slug) . "'>
              <th scope='row'><label for='$slug'>$caption</label></th>
              <td><input
                      name='" . esc_attr("{$this->db_slug}__{$slug}") . "'
                      id='" . esc_attr($slug) . "'
                      type='checkbox'
                      data-tippy-content='" . esc_attr(
                        /* translators: 1: field name */
                        sprintf(_x("Toggle: %s", "setting-page", "blackswan-telegram"), $caption)
                      ) . "' value='" . esc_attr($value) . "'
                      " . checked($value, get_option("{$this->db_slug}__{$slug}", ""), false) . "
                      class='regular-text " . esc_attr($extra_class) . "' " . esc_attr($extra_html) . " />
              <p class='description'>" . ($desc) . "</p></td></tr>";
    }
    protected function print_setting_select($data) {
      extract(wp_parse_args($data, array(
        "slug"        => "",
        "caption"     => "",
        "options"     => array(),
        "desc"        => "",
        "extra_html"  => "",
        "extra_class" => "",
      )));
      echo "<tr class='type-select $extra_class " . sanitize_title($slug) . "'>
              <th scope='row'><label for='$slug'>$caption</label></th>
              <td><select
                      name='" . esc_attr("{$this->db_slug}__{$slug}") . "'
                      id='" . esc_attr($slug) . "'
                      data-tippy-content='" . esc_attr(
                        /* translators: 1: field name */
                        sprintf(_x("Choose %s", "setting-page", "blackswan-telegram"), $caption)
                      ) . "' class='regular-text " . esc_attr($extra_class) . "' " . esc_attr($extra_html) . ">";
      foreach ($options as $key => $value) {
        if ($key == "EMPTY") {
          $key = "";
        }
        echo "<option value='" . esc_attr($key) . "' " . selected(get_option("{$this->db_slug}__{$slug}", ""), $key, false) . ">" . esc_html($value) . "</option>";
      }
      echo "</select><p class='description'>" . ($desc) . "</p></td></tr>";
    }
    protected function print_setting_textarea($data) {
      extract(wp_parse_args($data, array(
        "slug" => "",
        "caption" => "",
        "style" => "",
        "desc" => "",
        "rows" => "4",
        "extra_html"  => "",
        "extra_class" => "",
      )));
      echo "<tr class='type-textarea $extra_class" . sanitize_title($slug) . "'>
              <th scope='row'><label for='$slug'>$caption</label></th>
              <td><textarea
                      name='" . esc_attr("{$this->db_slug}__{$slug}") . "'
                      id='" . esc_attr($slug) . "'
                      placeholder='" . esc_attr($caption) . "'
                      data-tippy-content='" . esc_attr(
                        /* translators: 1: field name */
                        sprintf(_x("Enter %s", "setting-page", "blackswan-telegram"), $caption)
                        ) . "' rows='" . esc_attr($rows) . "'
                      style='" . esc_attr($style) . "'
                      class='regular-text " . esc_attr($extra_class) . "' " . esc_attr($extra_html) . " >" . (get_option("{$this->db_slug}__{$slug}", "") ?: "") . "</textarea>
              <p class='description'>" . ($desc) . "</p></td></tr>";
    }
    protected function print_setting_editor($data) {
      extract(wp_parse_args($data, array(
        "slug"        => "",
        "caption"     => "",
        "options"     => array(),
        "desc"        => "",
        "extra_class" => "",
      )));
      $editor_settings = array_merge($options, array(
        'editor_height' => 150,    // (number) Editor height in pixels
        'media_buttons' => false,  // (bool) Whether to show the Add Media/other media buttons.
        'teeny'         => false,  // (bool) Whether to output the minimal editor config. Examples include Press This and the Comment editor. Default false.
        'tinymce'       => true,   // (bool|array) Whether to load TinyMCE. Can be used to pass settings directly to TinyMCE using an array. Default true.
        'quicktags'     => false,  // (bool|array) Whether to load Quicktags. Can be used to pass settings directly to Quicktags using an array. Default true.
        'editor_class'  => "",     // (string) Extra classes to add to the editor textarea element. Default empty.
        'textarea_name' => "{$this->db_slug}__{$slug}",
      ));

      $editor_id = strtolower(str_replace(array('-', '_', ' ', '*'), '', $slug));
      echo "<tr class='type-editor $extra_class " . sanitize_title($slug) . "'>
      <th scope='row'><label for='$slug'>$caption</label></th><td>";
      wp_editor((get_option("{$this->db_slug}__{$slug}", "") ?: ""), $editor_id, $editor_settings);

      echo "<p class='description'>" . ($desc) . "</p></td></tr>";
    }
    #endregion
  }
}
new class_setting;