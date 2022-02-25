
<?php require "static/header.html"; ?>
<title> <?php echo $_REQUEST["q"]; ?> - LibreX</title>
</head>
    <body>
        <form class="sub-search-container" method="post" enctype="multipart/form-data" autocomplete="off">
            <a href="/"><img id="logo" src="static/images/librex.png" alt="librex"></a>
            <input type="text" name="q" 
                <?php
                    $query = trim($_REQUEST["q"]);

                    if (1 > strlen($query) || strlen($query) > 256)
                    {
                        header("Location: /");
                        die();
                    } 
 
                    echo "value=\"$query\"";
                ?>
            >
            <br>
            <?php
                $type = $_REQUEST["type"];
                echo "<input type=\"hidden\" name=\"type\" value=\"$type\"/>";
            ?>
            <button type="submit" style="display:none;"></button>
            <input type="hidden" name="p" value="0">
            <div class="sub-search-button-wrapper">
                <button name="type" value="0"><img src="static/images/text_result.png">Text</button>
                <button name="type" value="1"><img src="static/images/image_result.png">Images</button>
                <button name="type" value="2"><img src="static/images/video_result.png">Videos</button>
                <button name="type" value="3"><img src="static/images/torrent_result.png">Torrents</button>
            </div>
            
        <hr>
        </form>

        <?php
            require "config.php";

            function check_for_special_search($query)
            {
                $query_lower = strtolower($query);
        
                if (strpos($query_lower, "to"))
                {
                    require "engines/special/currency.php";
                    currency_results($query);
                }
                else if (strpos($query_lower, "mean"))
                {
                    require "engines/special/definition.php";
                    definition_results($query);
                }
                else if (3 > count(explode(" ", $query))) // long queries usually wont return a wiki result thats why this check exists
                {
                    require "engines/special/wikipedia.php";
                    wikipedia_results($query_lower);
                    return;
                }
            }

            $page = isset($_REQUEST["p"]) ? (int) $_REQUEST["p"] : 0;
            $type = isset($_REQUEST["type"]) ? (int) $_REQUEST["type"] : 0;
            
            $query_encoded = urlencode($query);

            switch ($type)
            {
                case 0:
                    require "engines/google/text.php";
                    $results = get_text_results($query_encoded, $page);
                    if ($page == 0)
                        check_for_special_search($query);
                    print_text_results($results);
                    break;

                case 1:
                    require "engines/google/image.php";
                    $results = get_image_results($query_encoded);
                    print_image_results($results);
                    break;

                case 2:
                    require "engines/google/video.php";
                    $results = get_video_results($query_encoded, $page);
                    print_video_results($results);
                    break;

                case 3:
                    if ($config_disable_bittorent_search)
                        echo "<p class=\"text-result-container\">The host disabled this feature! :C</p>";
                    else
                    {
                        require "engines/bittorrent/merge.php";
                        $results = get_merged_torrent_results($query_encoded);
                        print_merged_torrent_results($results);
                        break;
                    }
                    
                    break;

                default:
                    require "engines/google/text.php";
                    $results = get_text_results($query_encoded, $page);
                    print_text_results($results);
                    break;
            }


            if ($type == 0 || $type == 2 )
            {
                echo "<div class=\"next-page-button-wrapper\">";

                    if ($page != 0) 
                    {
                        print_next_page_button("&lt;&lt;", 0, $query, $type); 
                        print_next_page_button("&lt;", $page - 10, $query, $type);
                    }
                    
                    for ($i=$page / 10; $page / 10 + 10 > $i; $i++)
                        print_next_page_button($i + 1, $i * 10, $query, $type);

                    print_next_page_button("&gt;", $page + 10, $query, $type);

                echo "</div>";
            }
        ?>

<?php require "static/footer.html"; ?>