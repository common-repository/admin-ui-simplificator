<div class="wrap">
    <div class="webweb_wp_admin_ui_simplificator_admin">
        <div class="main_content">
            <h2>Help</h2>

            <h3>What does the plugin do?</h3>
            <p>
                The plugin simplifies the WordPress admin user interface by hiding most of the WordPress menus.
                This doesn't mean that the functionality won't be accessible. It will be just hidden.
                Also the admin navigation bar is cleaned as well.
                The plugin is intended to be used by developers/designers who manage WordPress on behalf of their clients.
                Clients don't need to know or see anything about plugins, themes, available updates etc.
            </p>

            <h3>How to use the plugin</h3>
            <p>
                <ol>
                    <li>Install and Activate the plugin</li>
                    <li>Create a separate account for your client</li>
                    <li>Everybody except the user who activated the plugin will sees the simplified WordPress admin area</li>
                </ol>
            </p>
			
			<h3>How to get support</h3>
			<div class="update-nag">
				
				<p>
					<strong>Support is handled in our own support forums <a href="https://club.orbisius.com/forums/" target="_blank">https://club.orbisius.com/forums/</a>
					<br/>Please do NOT use the WordPress forums to seek support.
					</strong>
				</p>
			</div>

            <h3>Demo</h3>

            <p>
                Link: <a href="https://www.youtube.com/watch?v=xQLe2uxmWiA&hd=1" target="_blank" title="[opens in a new and bigger tab/window]">https://www.youtube.com/watch?v=xQLe2uxmWiA&hd=1</a>
                <p>
                    <iframe width="640" height="480" src="https://www.youtube.com/embed/xQLe2uxmWiA?hl=en&fs=1" frameborder="0" allowfullscreen></iframe>
                </p>

                <?php
                $app_link = 'https://www.youtube.com/embed/xQLe2uxmWiA?hl=en&fs=1';
                $app_title = $orb_wp_simple_ui_obj->get('app_title');
                $app_descr = $orb_wp_simple_ui_obj->get('plugin_description');
                ?>
                <p>Share this video:
                    <!-- AddThis Button BEGIN -->
                    <div class="addthis_toolbox addthis_default_style addthis_16x16_style">
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
            </p>
        </div> <!-- /main_content -->

        <?php Orbisius_WP_Admin_UI_SimplificatorUtil::output_orb_widget('author'); ?>
    </div> <!-- /webweb_wp_admin_ui_simplificator_admin -->
</div>
