# Category Custom Post Order for WordPress
By using this plugin you can easily reorder your category psot . 

# Rember this plugin only for wordpress Developer 


# Query Sample 

```
query_posts(array(
        'posts_per_page'=>4,
        "orderby" => '_pposition_2', //2 is category id
        "meta_key" => '_pposition_2', //2 is category id
        "order" => 'ASC'
    ));
    while (have_posts()){
        the_post();
        the_title();
        echo "<br>";

    }

    wp_reset_query();
``` 

if your category id 1 use `_pposition_1`  if your category id 5 use `_pposition_5`



I hope this plugin help many developer. 