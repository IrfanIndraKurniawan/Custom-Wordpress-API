<?php
//----------------------------------------------------------------------
// Custom API for My FLutter Project
// Add last line Function.php

add_action('rest_api_init', function () {
    register_rest_route('wp/v2', 'latest-posts', [
        'method' => 'GET',
        'callback' => 'get_latest_posts_by_category',
    ]);

    register_rest_route('wp/v2', 'post-details', [
        'method' => 'GET',
        'callback' => 'get_post_by_id',
    ]);
});

function get_latest_posts_by_category($request)
{
    $args = ['category' => $request['category_id']];
    $posts = get_posts($args);

    if (empty($posts)) {
        return new WP_Error('empty_category', 'Tidak ada artikel pada kategori ini', ['status' => 404]);
    }

    $post_list = [];

    foreach ($posts as $post) {
        $post_categories = wp_get_post_categories($post->ID);
        $cats = [];
        foreach ($post_categories as $c) {
            $cat = get_category($c);
            $cats[] = $cat->name;
        }

        $post_list[] = [
            "id"            => $post->ID,
            "post_date"     => $post->post_date,
            "title"         => $post->post_title,
            "category_name" => array_values($cats)[0],
            "image_url"     => get_post_feature_image($post->ID),
        ];
    }

    $response = new WP_REST_Response($post_list);
    $response->set_status(200);
    return $response;
}

function get_post_by_id($request)
{
    $post = get_post($request["id"]);
    $post_categories = wp_get_post_categories($post->ID);

    $post_output = [
        "id"            => $post->ID,
        "post_date"     => $post->post_date,
        "title"         => $post->post_title,
        "category_name" => get_category($post_categories[0])->name,
        "post_content"  => $post->post_content,
        "image_url"     => get_post_feature_image($post->ID),
    ];

    $response = new WP_REST_Response($post_output);
    $response->set_status(200);
    return $response;
}

function get_post_feature_image($post_id)
{
    $args = [
        'posts_per_page'    => 1,
        'order'             => 'ASC',
        'post_mime_type'    => 'image',
        'post_parejt'       => $post_id,
        'post_status'       => null,
        'post_type'         => 'attachment',
    ];

    $attachments = get_children($args);
    return wp_get_attachment_image_src(array_values($attachments)[0]->ID, 'full')[0];
}
