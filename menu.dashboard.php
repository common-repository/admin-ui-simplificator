<?php 
$opts = $orb_wp_simple_ui_obj->get_options();
?>

<div class="wrap">
    <div class="webweb_wp_admin_ui_simplificator_admin">
        <div class="main_content">
            <h2><?php echo __('Dashboard', 'webweb_member_status') ?></h2>

            <div class="app_message_box app_error">
                Please check the <a href="<?php echo $orb_wp_simple_ui_obj->get('plugin_admin_url_prefix');?>/menu.support.php">Help</a> section if you need instructions how to use this plugin.
            </div>

            <table class="app_table">
                <tr>
                    <td>Plugin Status</td>
                    <td>
                        <?php if (empty($opts['status'])) : ?>
                            <img class="status_image" src="<?php echo $orb_wp_simple_ui_obj->get('plugin_url');?>/zzz_media/cross.png" alt="Disabled" />
                                 <?php echo $orb_wp_simple_ui_obj->m('Disabled') ?>
                        <?php else : ?>
                            <img class="status_image" src="<?php echo $orb_wp_simple_ui_obj->get('plugin_url');?>/zzz_media/tick.png" alt="Enabled" />
                                <?php echo $orb_wp_simple_ui_obj->m('Enabled', 1) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <?php
                echo $orb_wp_simple_ui_obj->generate_donate_box();
            ?>
            
            <?php
                echo $orb_wp_simple_ui_obj->generate_newsletter_box(array('src2' => 'dashboard'));
            ?>

           
        </div> <!-- /main_content -->

        <div class="admin_sidebar">
                <?php
                $app_link = $orb_wp_simple_ui_obj->get('plugin_home_page');
                $app_title = $orb_wp_simple_ui_obj->get('app_title');
                $app_descr = $orb_wp_simple_ui_obj->get('plugin_description');
                ?>
                <p>&nbsp;</p>
                <h3>Love this plugin? Share it with your friends!</h3>
                <p>
                    <!-- AddThis Button BEGIN -->
                    <div class="addthis_toolbox addthis_default_style addthis_32x32_style">
                    <a class="addthis_button_facebook" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_twitter" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_google_plusone" g:plusone:count="false" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_linkedin" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_email" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_myspace" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_google" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_digg" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_delicious" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_stumbleupon" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_tumblr" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_favorites" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
                    <a class="addthis_button_compact"></a>
                    </div>
                    <!-- The JS code is in the footer -->
                </p>

                <script type="text/javascript">
                var addthis_config = {"data_track_clickback":true};
                var addthis_share = {
                templates: { twitter: 'Check out {{title}} @ {{lurl}} (from @orbisius)' }
                }
                </script>
                <!-- AddThis Button START part2 -->
                <script type="text/javascript" src="https://s7.addthis.com/js/250/addthis_widget.js#pubid=lordspace"></script>
                <!-- AddThis Button END part2 -->

                <h3>Facebook Share</h3>
                <div id="fb-root"></div><script src="//connect.facebook.net/en_US/all.js#appId=163116837104802&amp;xfbml=1"></script>
                    <fb:like href="<?php echo $orb_wp_simple_ui_obj->get('plugin_home_page');?>" send="true" width="450" show_faces="true" font="arial"></fb:like>

                <h3>Comment</h3>
                <div id="fb-root"></div><script src="//connect.facebook.net/en_US/all.js#xfbml=1"></script><fb:comments href="<?php echo $orb_wp_simple_ui_obj->get('plugin_home_page');?>" num_posts="5" width="250"></fb:comments>
        </div> <!-- /sidebar -->
    </div> <!-- /webweb_wp_admin_ui_simplificator_admin -->
</div> <!-- /wrap -->
