<?php
/*
 * Elementor Forms Google Sheet Connector Dashboard Widget
 * @since 1.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals
?>

<div class="dashboard-content">

  <?php
  $elementorgs_connector_service = new GSC_Elementor_Integration();

    /*
     * Get all feeds connected to Google Sheets.
     */
    $forms_feeds_list = $elementorgs_connector_service->get_forms_feeds_connected_to_sheet();
    ?>

    <div class="main-content">
      <div>

        <h3>
          <?php
          echo esc_html__(
            'Elementor Forms Connected Sheets using Pagebuilder',
            'gsheetconnector-for-elementor-forms'
          );
          ?>
        </h3>

        <style>
          .widget-table {
            border: 1px solid #eee;
            width: 100%;
          }

          .widget-table th {
            text-align: left;
            background: #eee;
            padding: 5px 8px;
            border-bottom: 1px solid #eee;
          }

          .widget-table td {
            text-align: left;
            background: #fff;
            padding: 5px 8px;
            word-wrap: break-word;
          }

          .widget-table td:nth-child(1) {
            width: 50%;
          }

          .widget-table a {
            text-decoration: none;
          }

          .widget-table a:hover {
            text-decoration: underline;
          }

          .widget-table .gselef-not-connected {
            color: #777;
          }
        </style>

        <table class="widget-table">

          <tbody>

            <tr>
              <th>
                <?php
                echo esc_html__(
                  'Feed Name',
                  'gsheetconnector-for-elementor-forms'
                );
                ?>
              </th>

              <th>
                <?php
                echo esc_html__(
                  'Sheet URL',
                  'gsheetconnector-for-elementor-forms'
                );
                ?>
              </th>
            </tr>

            <?php
            if (!empty($forms_feeds_list)) :

              foreach ($forms_feeds_list as $feed_value) :

                            /*
                             * post_id = Elementor page/post ID.
                             * meta_id = unique feed meta ID.
                             * meta_key = feed name such as feed1, feed2.
                             */
                            $form_id = !empty($feed_value->post_id)
                            ? absint($feed_value->post_id)
                            : 0;

                            $feed_id = !empty($feed_value->meta_id)
                            ? absint($feed_value->meta_id)
                            : 0;

                            /*
                             * Use the meta key as the feed name.
                             *
                             * Example:
                             * feed1
                             * feed2
                             */
                            $feed_name = !empty($feed_value->meta_key)
                            ? $feed_value->meta_key
                            : '';

                            /*
                             * Skip invalid feed records.
                             */
                            if (empty($form_id) || empty($feed_id)) {
                              continue;
                            }

                            /*
                             * Get the feed configuration.
                             *
                             * First try the feed meta ID because the
                             * feed configuration can be stored against
                             * the individual feed ID.
                             */
                            $feed_data = get_post_meta(
                              $feed_id,
                              'gscele_form_feeds',
                              true
                            );

                            /*
                             * Fallback to the Elementor post/page ID
                             * if no feed configuration was found.
                             */
                            if (!is_array($feed_data) || empty($feed_data)) {
                              $feed_data = get_post_meta(
                                $form_id,
                                'gscele_form_feeds',
                                true
                              );
                            }

                            /*
                             * Make sure feed data is an array.
                             */
                            if (!is_array($feed_data)) {
                              $feed_data = array();
                            }

                            /*
                             * If feed-specific settings are stored under
                             * feed1/feed2/etc., load the current feed data.
                             */
                            if (
                              !empty($feed_name) &&
                              isset($feed_data[$feed_name]) &&
                              is_array($feed_data[$feed_name])
                            ) {
                              $feed_data = $feed_data[$feed_name];
                            }

                            /*
                             * Get Google Sheet ID.
                             */
                            $sheet_id = !empty($feed_data['sheet-id'])
                            ? $feed_data['sheet-id']
                            : '';

                            /*
                             * Get Google Sheet tab ID.
                             */
                            $tab_id = !empty($feed_data['tab-id'])
                            ? $feed_data['tab-id']
                            : '0';

                            /*
                             * Get Google Sheet name.
                             */
                            $sheet_name = !empty($feed_data['sheet-name'])
                            ? $feed_data['sheet-name']
                            : '';

                            /*
                             * If sheet name is not available, use a
                             * readable fallback.
                             */
                            if (empty($sheet_name) && !empty($sheet_id)) {
                              $sheet_name = __('Google Sheet', 'gsheetconnector-for-elementor-forms');
                            }
                            ?>

                            <tr>

                              <!-- Feed Name -->
                              <td>
                                <?php
                                echo esc_html($feed_name);
                                ?>
                              </td>

                              <!-- Sheet Name -->
                              <td>

                                <?php if (!empty($sheet_id)) : ?>

                                  <a
                                  href="<?php echo esc_url(
                                    'https://docs.google.com/spreadsheets/d/' .
                                    rawurlencode($sheet_id) .
                                    '/edit#gid=' .
                                    rawurlencode($tab_id)
                                    ); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    >
                                    <?php
                                    echo esc_html($sheet_name);
                                    ?>
                                  </a>

                                <?php else : ?>

                                  <span class="gselef-not-connected">
                                    <?php
                                    echo esc_html__(
                                      'Not connected',
                                      'gsheetconnector-for-elementor-forms'
                                    );
                                    ?>
                                  </span>

                                <?php endif; ?>

                              </td>

                            </tr>

                          <?php endforeach; ?>

                        <?php else : ?>

                          <tr>
                            <td colspan="2">
                              <?php
                              echo esc_html__(
                                'No Elementor Forms are connected with Google Sheets.',
                                'gsheetconnector-for-elementor-forms'
                              );
                              ?>
                            </td>
                          </tr>

                        <?php endif; ?>

                      </tbody>

                    </table>

                  </div>
                </div>

              </div>

              <style type="text/css">
                .postbox-header .hndle {
                  justify-content: flex-start !important;
                }
              </style>