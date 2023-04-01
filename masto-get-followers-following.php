<?php
    $url = trim(addslashes(strip_tags($_GET['url'])));
?>

<form action="" method="GET" name="form">
    <input type="text" name="url" placeholder="Profile URL..." value="<?php echo $url; ?>" size="100"><br><br>
    <button type="submit">Get Followers/Following</button>
</form>

<?php
    if(empty($url))
    {
        exit;
    }
    else
    {
        $explode_url = explode("@", $url);
        $mastodon_domain = $explode_url[0];
        $mastodon_username = $explode_url[1];
        $check = '/^[a-zA-Z0-9_]+/';

        if(filter_var($mastodon_domain, FILTER_VALIDATE_URL) AND preg_match($check, $mastodon_username))
        {
            $profile_url = $url;
        }
        else
        {
            echo "Forbidden value of GET variable";
            exit;
        }
    }


    // GET ACCOUNT ID
    $api_url = $mastodon_domain."/api/v1/accounts/lookup?acct=".$mastodon_username;

    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_URL, $api_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    $json = curl_exec($curl);
    $api_result = json_decode($json, true);
    $mastodon_id = $api_result['id'];

    if(empty($mastodon_id))
    {
        echo "Error while getting account ID, failed to connect to API";
        exit;
    }


    // FUNCTIONS
    function HeaderLink($curl, $header_line) {
        if(str_contains($header_line, "link:"))
        {
            $GLOBALS['link'] = $header_line;
        }
        return strlen($header_line);
    }


    // GET LIST OF FOLLOWERS
    $followers_counter = 0;
    $followers = array();
    $followers_ids = array();
    
    $api_url = $mastodon_domain."/api/v1/accounts/".$mastodon_id."/followers?limit=80";
    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_URL, $api_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_HEADERFUNCTION, "HeaderLink");
    $json = curl_exec($curl);
    $api_result = json_decode($json, true);

    foreach($api_result as $follow)
    {
        if(!in_array($follow['id'], $followers_ids))
        {
            $followers_ids[] = $follow['id'];
            $followers[] = array(
                "id" => $follow['id'], 
                "acct" => $follow['acct'],  
                "display_name" => $follow['display_name'],  
                "url" => $follow['url'],  
                "avatar" => $follow['avatar'],  
                "followers_count" => $follow['followers_count'],  
                "following_count" => $follow['following_count'],  
                "statuses_count" => $follow['statuses_count']
            );
            $followers_counter++;
        }
    }
    preg_match("(link: <(.+?)>; rel=\"next\", <.+?>; rel=\"prev\")is", $GLOBALS['link'], $temp);
    $api_url = $temp[1];

    while(!empty($api_url))
    {
        $curl = curl_init($api_url);
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, "HeaderLink");
        $json = curl_exec($curl);
        $api_result = json_decode($json, true);

        foreach($api_result as $follow)
        {
            if(!in_array($follower['id'], $followers_ids))
            {
                $followers_ids[] = $follow['id'];
                $followers[] = array(
                    "id" => $follow['id'], 
                    "acct" => $follow['acct'],  
                    "display_name" => $follow['display_name'],  
                    "url" => $follow['url'],  
                    "avatar" => $follow['avatar'],  
                    "followers_count" => $follow['followers_count'],  
                    "following_count" => $follow['following_count'],  
                    "statuses_count" => $follow['statuses_count']
                );
                $followers_counter++;
            }
        }
        preg_match("(link: <(.+?)>; rel=\"next\", <.+?>; rel=\"prev\")is", $GLOBALS['link'], $temp);
        $api_url = $temp[1];
    }


    // GET LIST OF FOLLOWING
    $following_counter = 0;
    $following = array();
    $following_ids = array();
    
    $api_url = $mastodon_domain."/api/v1/accounts/".$mastodon_id."/following?limit=80";
    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_URL, $api_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_HEADERFUNCTION, "HeaderLink");
    $json = curl_exec($curl);
    $api_result = json_decode($json, true);

    foreach($api_result as $follow)
    {
        if(!in_array($follow['id'], $following_ids))
        {
            $following_ids[] = $follow['id'];
            $following[] = array(
                "id" => $follow['id'], 
                "acct" => $follow['acct'],  
                "display_name" => $follow['display_name'],  
                "url" => $follow['url'],  
                "avatar" => $follow['avatar'],  
                "followers_count" => $follow['followers_count'],  
                "following_count" => $follow['following_count'],  
                "statuses_count" => $follow['statuses_count']
            );
            $following_counter++;
        }
    }
    preg_match("(link: <(.+?)>; rel=\"next\", <.+?>; rel=\"prev\")is", $GLOBALS['link'], $temp);
    $api_url = $temp[1];

    while(!empty($api_url))
    {
        $curl = curl_init($api_url);
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, "HeaderLink");
        $json = curl_exec($curl);
        $api_result = json_decode($json, true);

        foreach($api_result as $follow)
        {
            if(!in_array($follow['id'], $following_ids))
            {
                $following_ids[] = $follow['id'];
                $following[] = array(
                    "id" => $follow['id'], 
                    "acct" => $follow['acct'],  
                    "display_name" => $follow['display_name'],  
                    "url" => $follow['url'],  
                    "avatar" => $follow['avatar'],  
                    "followers_count" => $follow['followers_count'],  
                    "following_count" => $follow['following_count'],  
                    "statuses_count" => $follow['statuses_count']
                );
                $following_counter++;
            }
        }
        preg_match("(link: <(.+?)>; rel=\"next\", <.+?>; rel=\"prev\")is", $GLOBALS['link'], $temp);
        $api_url = $temp[1];
    }
?>

<h1>Followers</h1>
<b>Number of followers found:</b> <?php echo $followers_counter; ?><br><br>
<table>
    <tr>
        <th>Lp.</th>
        <th>Avatar</th>
        <th>ID</th>
        <th>Handle</th>
        <th>Name</th>
        <th>Followers</th>
        <th>Following</th>
        <th>Toots</th>
        <th>URL</th>
    </tr>
<?php
    $i = 1;
    foreach($followers as $follow)
    {
        echo "<tr>";
        echo "<td>".$i."</td>";
        echo "<td><img src=\"".$follow['avatar']."\" style=\"max-width: 50px; max-height: 50px;\" /></td>";
        echo "<td>".$follow['id']."</td>";
        echo "<td>".$follow['acct']."</td>";
        echo "<td>".$follow['display_name']."</td>";
        echo "<td>".$follow['followers_count']."</td>";
        echo "<td>".$follow['following_count']."</td>";
        echo "<td>".$follow['statuses_count']."</td>";
        echo "<td><a href=\"".$follow['url']."\">".$follow['url']."</a></td>";
        echo "</tr>";

        $i++;
    }
?>
</table>

<h1>Following</h1>
<b>Number of following found:</b> <?php echo $following_counter; ?><br><br>
<table>
    <tr>
        <th>Lp.</th>
        <th>Avatar</th>
        <th>ID</th>
        <th>Handle</th>
        <th>Name</th>
        <th>Followers</th>
        <th>Following</th>
        <th>Toots</th>
        <th>URL</th>
    </tr>
<?php
    $i = 1;
    foreach($following as $follow)
    {
        echo "<tr>";
        echo "<td>".$i."</td>";
        echo "<td><img src=\"".$follow['avatar']."\" style=\"max-width: 50px; max-height: 50px;\" /></td>";
        echo "<td>".$follow['id']."</td>";
        echo "<td>".$follow['acct']."</td>";
        echo "<td>".$follow['display_name']."</td>";
        echo "<td>".$follow['followers_count']."</td>";
        echo "<td>".$follow['following_count']."</td>";
        echo "<td>".$follow['statuses_count']."</td>";
        echo "<td><a href=\"".$follow['url']."\">".$follow['url']."</a></td>";
        echo "</tr>";

        $i++;
    }
?>
</table>
