<?php

// Control core classes for avoid errors
if (class_exists('CSF')) {

    //
    // Set a unique slug_like ID
    $prefix = 'mrv_notify_settings';

    /**
     *
     * @menu_parent argument examples.
     *
     * For Dashboard: 'index.php'
     * For Posts: 'edit.php'
     * For Media: 'upload.php'
     * For Pages: 'edit.php?post_type=page'
     * For Comments: 'edit-comments.php'
     * For Custom Post Types: 'edit.php?post_type=your_post_type'
     * For Appearance: 'themes.php'
     * For Plugins: 'plugins.php'
     * For Users: 'users.php'
     * For Tools: 'tools.php'
     * For Settings: 'options-general.php'
     *
     */
    CSF::createOptions($prefix, array(
        'menu_title' => 'About',
        'menu_slug' => 'meta-notify-settings',
        'menu_type' => 'submenu',
         'framework_title'         => 'Welcome To Meta Notify Plugin',
    'menu_capability'         => 'manage_options',
    'menu_icon'               => null,
    'menu_position'           => null,
    'menu_hidden'             => false,
    'show_reset_all'          => false,
    'show_reset_section'      => false,
    'show_footer'             => false,
    'show_search'             => false,
    'show_all_options'        => false,


    // theme and wrapper classname
    'nav'                     => 'inline',
    'theme'                   => 'light',
    'class'                   => '',
    ));

    //
    // Create a section
    CSF::createSection($prefix, array(
                'title' => __('Customization Settings', 'meta-notify'),
                'fields' => array(
                    array(
                        'id' => 'notify_font',
                        'title' => __('Notify Font', 'meta-notify'),
                        'type' => 'typography', // Do not add unnecessary typography settings
                       'font_weight' => false,
                        //'font_style'=>false,
                        'text_align' => false,
                        'text_transform' => false,
                        'subset' => false,
                        'letter_spacing' => false,
                        'preview' => false,
                        'default' => array(                                               
                            'font-family'        => 'Arial',
                            'font-style'         => 'Normal 400',
                            'font-size'          => '16',
                            'line-height'        => '2',
                            'font-weight'        => 'normal',
                            'color'              => 'black',
                            ),
                    ),

                    array(
                        'id' => 'notify_bg_color',
                        'type' => 'color',
                        'title' => __('Background Color', 'meta-notify'),
                        'default' => 'aliceblue',
                    ),
                    array(
                        'id' => 'button_bg_color',
                        'type' => 'color',
                        'title' => __('Button Background Color', 'meta-notify'),
                        'default' => 'aliceblue',
                    ),
                    array(
                        'id' => 'notify_border',
                        'type' => 'border',
                        'title' => 'Notification Box Border',
                        'default' => array(
                            'color'      => 'black',                                              
                            'style'      => 'solid',
                            'top'        => '2',
                            'right'      => '2',
                            'bottom'     => '2',
                            'left'       => '2',                                           
                        ),
                    ),
                    array(
                        'id' => 'connect_button_border',
                        'type' => 'border',
                        'title' => 'Connect Button Border',
                        'default' => array(
                            'color'      => 'black',                                              
                            'style'      => 'solid',
                            'top'        => '2',
                            'right'      => '2',
                            'bottom'     => '2',
                            'left'       => '2',                                           
                        ),
                    ),
                    array(
                        'id' => 'thanks_button_border',
                        'type' => 'border',
                        'title' => 'Thanks Button Border',
                        'default' => array(
                            'color'      => 'black',                                              
                            'style'      => 'solid',
                            'top'        => '2',
                            'right'      => '2',
                            'bottom'     => '2',
                            'left'       => '2',                                           
                        ),
                    ),

            ),
        ),
    );

    // //
    // // Create a section
    // CSF::createSection($prefix, array(
    //     'title' => 'Getting Started',
    //     'fields' => array(

    //          // A Callback Field Example
    //         array(
    //         'type'     => 'callback',
    //         'function' => 'mrv_getting_start',
    //         ),

    //     ),
    // ));
}
