<?php
 
/*
Plugin Name: Newsletter HTML Generator
Plugin URI: http://n.infobusiness2.ru/newsletter-html-generator/
Description: Extracts title, teaser (or excerpt), author name, featured image, permalink, shortlink, date from current post and generates full HTML-code of ready to send newsletter based on templates you provide. You just copy and paste the final HTML-code in your favorite newsletter sending service like Mailchimp, GetResponse, Campaign Monitor, etc.
Author: Konstantin Benko
Version: 1.1.4
Author URI: https://facebook.com/ekosteg
*/

// Adds a meta box to the post editing screen
add_action( 'add_meta_boxes', 'kos_newshtml' );
function kos_newshtml() {
    add_meta_box( 'kos_newshtml_meta', __( 'Newsletter HTML', 'kos_textdomain' ), 'kos_newshtml_meta_callback', 'post', 'normal', 'high' );
}
//Outputs the content of the meta box
function kos_newshtml_meta_callback( $post ) {
    global $pagenow;
    if ( $pagenow == 'post-new.php' ) {
        echo 'You have to save/update post before generating Newsletter HTML';
    }
    else {
        $query = new WP_Query( array( 'post_type' => 'email-templates' ) );
        $html = '<p>Select newsletter template: ';
        $html .= '<select id="templates-select" onchange="var code = decodeURIComponent(jQuery(\'#templates-select\').val()); document.getElementById(\'ipreview\').srcdoc = code; jQuery(\'#preview\').show(); jQuery(\'#code\').val(\'\')">';
        $html .= '<option></option>';
        if ( count( $query ) ) {
            $shortlink = wp_get_shortlink();
            $permalink = urldecode( get_permalink() );
            $title = $post->post_title;
            $content = $post->post_content;
            preg_match( '/(.*)<!--more-->/s', $content, $matches );
            $teaser = strip_tags( count( $matches ) ? $matches[0] : $post->post_excerpt );
						$first10words = str_replace('"', '', implode(' ', array_slice(explode(' ', $teaser), 0, 10)));
            $image = wp_get_attachment_url( get_post_thumbnail_id() );
            $author = get_the_author();
            foreach ( $query->posts as $template ) {
                $template->post_content = str_replace( '{{{title}}}', $title, $template->post_content );
                $template->post_content = str_replace( '{{{teaser}}}', $teaser, $template->post_content );
                $template->post_content = str_replace( '{{{first10words}}}', $first10words, $template->post_content );
                $template->post_content = str_replace( '{{{author}}}', $author, $template->post_content );
                $template->post_content = str_replace( '{{{shortlink}}}', $shortlink, $template->post_content );
                $template->post_content = str_replace( '{{{permalink}}}', $permalink, $template->post_content );
                $template->post_content = str_replace( '{{{image}}}', $image, $template->post_content );
                $template->post_content = str_replace( '{{{date}}}', get_the_date('F j, Y'), $template->post_content );
                $html .= '<option value="'. strtr( rawurlencode( $template->post_content ), array( '%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')' ) ). '">'. $template->post_title. '</option>';
            }
        }
        $html .= '</select> <small>*You can <a target="_blank" href="/wp-admin/edit.php?post_type=email-templates">create and edit templates here</a>.</small></p>';
				$html .= '<span id="preview" style="display:none;"><p>Here is the preview of your newsletter:<br><iframe id="ipreview" style="width:100%; height:500px;"></iframe></p><small>Advanced tip: if you add <a href="http://www.w3schools.com/tags/att_global_contenteditable.asp" target="_blank">contenteditable atribute</a> to some elements of your template – you will have the possibility to edit your newsletter right in the preview.</small><p><button class="button" onclick="var resultcode = finalResultCode(); jQuery(\'#code\').val(resultcode);jQuery(\'#results\').show();jQuery(\'#code\').select();return false;">Looks fine? Get ready-to-send HTML code</button></p></span>';
				$html .= '<p id="results" style="display:none;">Your "Ready to Send" newsletter HTML code:<br><textarea id="code" style="width:100%; height:150px;"></textarea><br><small>Press <kbd>Ctrl</kbd>+<kbd>C</kbd> to copy the code. Then use it for any newsletter service provider like Mailchimp, GetResponse, etc.</small></p>';
				$html .= '<script>
					function finalResultCode() {
						var final = jQuery(\'#ipreview\').contents()[0].documentElement.outerHTML;
						if (code.indexOf(\'<!DOCTYPE\') != -1) {
							final = final.substr(25,final.length-40);
						} else {
							final = \'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">\' + final;
						}
						final=final.replace(/(<[^>]+)( contenteditable="[^"]*")/g,"$1");
						final=final.replace(/(<[^>]+)( contenteditable)/g,"$1");
						final=final.replace(\'image=""\',\'image\');
						return final;
					}
				</script>';
        echo $html;
    }
}

// Adds help meta box to the post editing screen
add_action( 'add_meta_boxes', 'kos_newshtml_help' );
function kos_newshtml_help() {
    add_meta_box( 'kos_newshtml_help_meta', __( 'Newsletter HTML Help', 'textdomain' ), 'kos_newshtml_help_meta_callback', 'email-templates', 'side' );
}
//Outputs the content of the meta box
function kos_newshtml_help_meta_callback( $post ) { ?>
    <ol>
        <li>Paste the template code from your newsletter service provider (like Mailchimp)</li>
        <li>Insert any of these snippets to the appropriate places of your template:
            <br><kbd>{{{title}}}</kbd>
            <br><kbd>{{{teaser}}}</kbd> – will use excerpt or the text above ReadMore tag
						<br><kbd>{{{author}}}</kbd>
            <br><kbd>{{{image}}}</kbd> – will use featured image
            <br><kbd>{{{permalink}}}</kbd>
            <br><kbd>{{{shortlink}}}</kbd>
            <br><kbd>{{{date}}}</kbd>
						<br><kbd>{{{first10words}}}</kbd> – first 10 words of teaser – useful for inserting to invisible first element, so google will use it as a snippet
            <br>The snippets will be replaced by actual data from your posts.</li>
        <li>Save the template.</li>
    </ol>
    <p>Now you can use this template. Open (edit) any of your regular posts and generate ready-to-send HTML newsletter code.</p>
<?php }


// Creates Email Templates Custom Post Type
add_action( 'init', 'kos_email_templates_init' );
function kos_email_templates_init() {
    $args = array(
        'label' => 'Email Templates',
        'description' => 'Full HTML code for newsletter services like Mailchimp',
        'public' => false,
        'show_ui' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => array( 'slug' => 'email-templates' ),
        'with_front' => false,
        'pages' => false,
        'query_var' => true,
        'delete_with_user' => false,
        'menu_icon' => 'dashicons-email',
        'supports' => array(
            'title',
            'editor',
            'revisions' )
    );
    register_post_type( 'email-templates', $args );
}
//disables rich-text-editor
add_filter( 'user_can_richedit', 'kos_disable_for_cpt' );
function kos_disable_for_cpt( $default ) {
	global $post;
	if ( 'email-templates' == get_post_type( $post ) )
		return false;
	return $default;
}


// Creates initial email template example on plugin activation
register_activation_hook( __FILE__, 'kos_newshtml_activation' );
function kos_newshtml_activation() {
    wp_insert_post (array( 
        'post_status' => 'publish',
        'post_type' => 'email-templates',
        'post_title' => 'Newsletter template example',
        'post_content' => '<html>
            <head>
                <title>{{{title}}}</title>
                <meta http-equiv="Content-type" content="text/html; charset=utf-8">

            <style type="text/css">
                body,#wrap{
                    text-align:center;
                    margin:0px;
                    background-color:#FFFFFF;
                }
            /*
            @tab Top bar
            @section top bar
            @tip Choose a set of colors that look good with the colors of your logo image or text header.
            */
                #header{
                    /*@tab Top bar
            @section top bar
            @tip Choose a set of colors that look good with the colors of your logo image or text header.*/background-color:#FFFFFF;
                    margin:0px;
                    /*@editable*/padding:10px;
                    /*@editable*/color:#666;
                    /*@editable*/font-size:11px;
                    /*@editable*/font-family:Arial;
                    /*@editable*/font-weight:normal;
                    /*@editable*/text-align:center;
                    /*@editable*/text-transform:lowercase;
                    /*@editable*/border:none 0px #FFF;
                }
            /*
            @tab Top bar
            @section top bar links
            @tip Choose a set of colors that look good with the colors of your logo image or text header.
            */
                #header a,#header a:link,#header a:visited{
                    /*@editable*/color:#666;
                    /*@editable*/text-decoration:underline;
                    /*@editable*/font-weight:normal;
                }
            /*
            @tab Body
            @section default text
            @tip This is the base font for the content of the email
            */
                #layout{
                    /*@tab Body
            @section default text
            @tip This is the base font for the content of the email*/margin:0px auto;
                    /*@editable*/text-align:center;
                    /*@editable*/font-family:Georgia;
                    /*@editable*/color:#404040;
                    /*@editable*/line-height:160%;
                    font-size:16px;
                }
            /*
            @tab Body
            @section appointment detail
            @tip appointment detail styles
            */
                #appointment{
                    /*@editable*/color:#666;
                    /*@editable*/font-size:18px;
                    /*@editable*/font-weight:normal;
                    /*@editable*/font-family:Georgia;
                    /*@editable*/text-align:center;
                    /*@editable*/padding:0px 0px 40px 0px;
                }
            /*
            @tab Body
            @section title style
            @tip Primary headline
            @theme title
            */
                .primary-heading{
                    /*@editable*/font-size:54px;
                    /*@editable*/color:#000;
                    /*@editable*/font-weight:normal;
                    /*@editable*/font-family:Georgia;
                    /*@editable*/line-height:120%;
                    /*@editable*/margin:10px 0;
                }
            /*
            @tab Body
            @section subtitle style
            @tip Secondary headline
            @theme subtitle
            */
                .secondary-heading{
                    /*@editable*/color:#000;
                    /*@editable*/font-size:24px;
                    /*@editable*/font-weight:normal;
                    /*@editable*/font-style:normal;
                    /*@editable*/font-family:Georgia;
                    /*@editable*/margin:30px 0 10px 0;
                }
            /*
            @tab Footer
            @section footer
            @tip Use the same color as your background to create the page curl
            @theme footer
            */
                #footer{
                    /*@tab Footer
            @section footer
            @tip Use the same color as your background to create the page curl
            @theme footer*/background-color:#FFFFFF;
                    /*@editable*/border-top:1px solid #CCC;
                    /*@editable*/padding:20px;
                    /*@editable*/font-size:10px;
                    /*@editable*/color:#666;
                    /*@editable*/line-height:100%;
                    /*@editable*/font-family:Arial;
                    /*@editable*/text-align:center;
                }
            /*
            @tab Footer
            @section link style
            @tip Specify a color for your footer hyperlinks.
            @theme link_footer
            */
                #footer a{
                    /*@editable*/color:#666;
                    /*@editable*/text-decoration:underline;
                    /*@editable*/font-weight:normal;
                }
            /*
            @tab Links
            @section link style
            @tip Specify a color for all the hyperlinks in your email.
            @theme link
            */
                a,a:link,a:visited{
                    /*@editable*/color:#666;
                    /*@editable*/text-decoration:underline;
                    /*@editable*/font-weight:normal;
                }
            </style></head>
            <body class="background">
                <div id="wrap" class="background">
                    <table id="layout" border="0" cellspacing="0" cellpadding="0" width="600" class="layout_background">
                        <tr>
                            <td id="header" mc:edit="header" colspan="3">
                                <!--*|IFNOT:ARCHIVE_PAGE|*-->
                                <span>Email not displaying correctly? <a href="*|ARCHIVE|*" target="_blank">View it in your browser.</a></span>
                                <!-- *|END:IF|* -->
                            </td>
                        </tr>
                        <tr>
                            <td id="lead_image" colspan="3">
                                <img src="{{{image}}}">
                            </td>
                        </tr>
                        <tr>
                          <td id="lead_content" mc:edit="main" colspan="3">
                            <h1 class="primary-heading">{{{title}}}</h1>
                            <p>{{{teaser}}}</p>
                            <p><a href="{{{permalink}}}">Read more...</a></p>
                            <small>This post is written by {{{author}}}.</small>
                          </td>
                        </tr>
                            <td id="footer" mc:edit="footer" colspan="3" class="background">
                                <p><a href="*|UNSUB|*">Unsubscribe</a> *|EMAIL|* | <a href="*|UPDATE_PROFILE|*">Update your profile</a> | <a href="*|FORWARD|*">Forward to a friend</a></p>
                                *|IFNOT:ARCHIVE_PAGE|*
                                <p>*|LIST:DESCRIPTION|*</p>
                                <p>*|HTML:LIST_ADDRESS_HTML|*</p>
                                *|END:IF|*
                                <p>Copyright (C) *|CURRENT_YEAR|* *|LIST:COMPANY|* All rights reserved.</p>
                                *|IF:REWARDS|*
                                *|HTML:REWARDS|*
                                *|END:IF|*
                            </td>
                        </tr>
                    </table>
                </div>
                <span style="padding: 0px;"></span>
            </body>
            </html>'
    ));
}
register_deactivation_hook( __FILE__, 'kos_newshtml_deactivation' );
function kos_newshtml_deactivation () {
    $query = new WP_Query( array( 'post_type' => 'email-templates' ) );
    if ( count( $query ) ) {
        foreach ( $query->posts as $template ) {
            if ( $template->post_title == 'Newsletter template example' ) {
                wp_delete_post( $template->ID );
            } else {
                $template->post_status = 'draft';
                wp_update_post( $template );
            }
        }
    }
}