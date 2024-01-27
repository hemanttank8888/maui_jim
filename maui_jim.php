<?php
require_once 'Database.php';
class MauiJimmSpider
{
    protected $dataList = [];
    public function getHeader($cookies_str){
        $headers = [
            'cookie: ' . $cookies_str,
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];
        return $headers;
    }

    public function getCh($url)
    {
        $session = curl_init($url);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($session, CURLOPT_COOKIEJAR, "cookies.txt");
        curl_setopt($session, CURLOPT_COOKIEFILE, "cookies.txt");
        curl_setopt($session, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3");
        curl_setopt($session, CURLOPT_COOKIEFILE, ""); 
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        return $session;
    }

    public function getCookie()
    {
        $loginUrl = "https://trade.mauijim.com/mauijimb2bstorefront/en_US/login";
        $checkUrl = "https://trade.mauijim.com/mauijimb2bstorefront/en_US/j_spring_security_check";

        $session = $this->getCh($loginUrl);
        $response = curl_exec($session);

        $dom = new DOMDocument;
        @$dom->loadHTML($response);
        $xpath = new DOMXPath($dom);

        $form = $xpath->query('//form[@id="B2BLoginForm"]')->item(0);
        $loginData = [];

        foreach ($form->getElementsByTagName('input') as $input) {
            $name = $input->getAttribute('name');
            $value = $input->getAttribute('value');
            if ($name && $value) {
                $loginData[$name] = $value;
            }
        }

        $loginData["j_formusername"] = "joel@nadlanrealty.com";
        $loginData["j_username"] = "mj_trade_joel@nadlanrealty.com";
        $loginData["j_password"] = "f\$sSCvu7BV7!qEW";
        $loginData["b2bUnit"] = "";
        $loginData["_spring_security_remember_me"] = "False";

        curl_setopt($session, CURLOPT_URL, $checkUrl);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, http_build_query($loginData));
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($session);

        $cookies = curl_getinfo($session, CURLINFO_COOKIELIST);

        curl_close($session);
        return $cookies;
    }

    public function startRequests()
    {
        $newCookies = $this->getCookie();
        $cookiesStr = implode('; ', $newCookies);

        $url = 'https://trade.mauijim.com/mauijimb2bstorefront/en_US/c/b2bsun_mauijim/';

        $headers = [
            'cookie: ' . $cookiesStr,
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];

        $ch = $this->getCh($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        if ($response === false) {
            echo "cURL Error: " . curl_error($ch);
        } else {
            curl_close($ch);
            $this->parse($response, $url, $headers, $newCookies, 0);
        }
    }

    public function parse($response, $url, $headers, $cookies)
    {
        $brandNames = [];
        $doc = new DOMDocument();
        @$doc->loadHTML($response);
        $xpath = new DOMXPath($doc);
        $productLinks = $xpath->query('//div[@class="col-prodgrid"]/div/div[@class="product-image"][1]/div[1]/a[1]/@href');
        $cookieString = 'Language=en_US; Language=en_US; mauiJimB2BRememberMeStatusCookie=singleunit; mauijimb2bstorefrontRememberMe=bWpfdHJhZGVfam9lbCU0MG5hZGxhbnJlYWx0eS5jb206MTcwNDI4ODc3ODg0NDo5NjZhZDA5YWNlZGQ0OWJmZjk2ZDU5YzFjODZjZmFlMg; _hjSessionUser_320886=eyJpZCI6ImIxNTg2NjFkLTQ2ODEtNWY1Ny1iNGUzLWM5NDcxZjk0MGNkZSIsImNyZWF0ZWQiOjE3MDMwNDYyMDQ1MzEsImV4aXN0aW5nIjp0cnVlfQ==; ROUTE=.accstorefront-84659445b6-qgh8r; bm_mi=7E7FA8598C8BA6C03C91ADC0E0E71807~YAAQnLD3SF6mm2mMAQAAxoFthxadS0Rh9egFa7pSmj9c7SQKc0GnmAc8Rfl4xsb3vPt+Pl5NgclirtFgzdbcdPkYVxsLl1Omf5fkQajqgbNbBnr1Mk2lDaslUGysu9s+dbtYmkd/J4ZVbesIGzxIXEzsQe8NRYErz/B/kFHevCKxBg6Ik7TT0uMaEaMzTUZTjM/60tcVSCnsCJzTrwYEsCENd9FcvXnoPhpxhLK9EphoBCoegNt58pud19PA078DvEAZz/uT9fzeDMAPjr0O/pBiTJw/CwJZLqNz+rnnpcGScW6urroLEx15+bebYH0xM8oJRcszvaIU3lDiR63O9OkqaSC0n4DF/4hOYl+z2w==~1; _hjIncludedInSessionSample_320886=1; _hjAbsoluteSessionInProgress=0; _hjSession_320886=eyJpZCI6IjY5MzQ0ZTliLTRhYmUtNDlkMi1iNzhjLWFlMDM0NDY0OTU5ZCIsImMiOjE3MDMwNzkxNjAxNzEsInMiOjEsInIiOjEsInNiIjoxfQ==; ak_bmsc=B2D3943E881602AFE2FAF38907123C8D~000000000000000000000000000000~YAAQnLD3SEuzm2mMAQAACPFthxZNbEPwYwEFNPuSobMnkPbZkP2oy4/ohIWFlesqZjBQ49yKv0mhL1KcOz2+5pMZzONQ+D5pBBfRDWlC/Nz7PA5KfdyO5AtSdWD4FyNrY5G5KkP5dh8P2dwIdLg0CT3+ez4PzJ0IFB5Ob4y7RKmG0/ZI2z86RU2d+H6IaI+jqkV7/WkUDANUOa/yVwMUh+4BPRMwaeKRYnGtTdP3U0FlawlYYKBdNviZnZbVHByKLurRppF/5fpYEyjISSKncxz08zzA4w3YDAVZOrxml8OvN8+sO7tnqcSW+kjgwzrV8GiwRSH8CjLpt3aQX/BDrRSfzDwbEZMaDmsSqjpc4RNXiEIgpefsSDU7tSHuX6CiDm8LH/CvJAWhXcCeDIUgvlB8JRg144nMEa4ah7B8XT6xKLaB4IZhsZpebJOWz1gIi1m06Gt6XQ8H9hzAYnVNWBrQEBiQIKUar8YzQ8A4lqhV/sWlziZeD1t4KlONaub9j/Dtp2BxF5K+x2QKVEacORglBZWJeF+r6c1L1u1c3MWRTxlppDXOAA==; acceleratorSecureGUID=df36ee58eae7e4bbbdaf9ff0ce3ffa4d95710d2b; bm_sv=1BC9708413BDE07A78BB349C5CE6CA54~YAAQPgVaaAq4AFiMAQAAjJBwhxZU4akoB98qezr4Cec1Vq77bjROPXyUb2+2dQKrk5tLiJlinCoUkfh/pf8LIb7rJKsled7CXEiC0XyiX8ujzN5FCn5KSGcLK5ZM33UilDtit5I5H1TqDOkBaypB0rbkiD0uSRhyAyHshIojiC7rRTCsT1U3A6+/1EQeGtE9guS9tx0oZrc1n58UY9jdopmtHTdOYud50RMmdzik+mQBnEW6ZqmM1kbjIoxAf5nXufA=~1; mauijimb2bstorefrontRememberMe=bWpfdHJhZGVfam9lbCU0MG5hZGxhbnJlYWx0eS5jb206MTcwNDI4OTY5OTI5NzphNjM2MzUzOWE0N2E2YTkyZDEzMWY3Yjc4ZDM1MWI4Yg; acceleratorSecureGUID=cac7891552e5c24f922721ab59c15fa767ed7d4d';
        $newCookies = $this->getCookie();
        $cookiesStr = implode('; ', $newCookies);

        $combinedCookiesString = $cookieString . '; ' . implode('; ', $newCookies);

        $headers = $this->getHeader($combinedCookiesString);

        foreach ($productLinks as $link) {
            $href = $link->nodeValue;
            $url = 'https://trade.mauijim.com' . $href;
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);

            $info = curl_getinfo($ch);
            if ($response === false) {
                echo "cURL Error: " . curl_error($ch);
            } else {
                curl_close($ch);

                $this->getProduct($response, $url, $headers, $newCookies, 0);
            }
        }
    }

    public function getProduct($response, $headers, $cookies)
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($response);

        $xpath = new DOMXPath($doc);

        $dataDict = [];
        $dataDict['productName'] = trim($xpath->query("//div[@class='product-title']//h2/text()")->item(0)->nodeValue);
        $dataDict['lensMaterial'] = trim($xpath->query("//strong[contains(text(), 'Lens Material:')]/following-sibling::span/text()")->item(0)->nodeValue);
        $dataDict['mauiEvolution'] = trim($xpath->query("//strong[contains(text(), 'Maui Evolution:')]/following-sibling::text()")->item(0)->nodeValue);
        $dataDict['polycarbonate'] = trim($xpath->query("//strong[contains(text(), 'Polycarbonate:')]/following-sibling::text()")->item(0)->nodeValue);
        $dataDict['mauiBrilliant'] = trim($xpath->query("//strong[contains(text(), 'MauiBrilliant:')]/following-sibling::text()")->item(0)->nodeValue);

        $diveElements = $xpath->query("//fieldset/div[starts-with(@class, 'row body variantitem')]");

        $productVariants = [];

        foreach ($diveElements as $dive) {
            $variant = [];
            $variant['styleCode'] = trim($xpath->query(".//span[@class='style-number']/text()", $dive)->item(0)->nodeValue);
            $variant['frame'] = trim($xpath->query(".//span[@class='framecolor-label']/following-sibling::text()", $dive)->item(0)->nodeValue);
            $variant['lens'] = trim($xpath->query(".//span[@class='lenscolor-label']/following-sibling::text()", $dive)->item(0)->nodeValue);
            $variant['price'] = trim($xpath->query(".//span[@class='price-label']/following-sibling::text()", $dive)->item(0)->nodeValue);

            $productVariants[] = $variant;
        }


        foreach ($productVariants as $imageCode) {
            $parts = explode("-", $imageCode['styleCode']);
            $code = $parts[0];
            $cleanedCode = preg_replace("/[^0-9]/", "", $code);
            $reversedCode = strrev($code);
            $count = strcspn($reversedCode, '0123456789');
            $string = ($count > 0) ? substr($code, 0, -$count) : $code;
            $parts = explode("-", $imageCode['styleCode']);
            $change_code = $parts[1];
            $image_code1 = $string . '-' . $change_code;
            if ($cleanedCode === "") {
                $parts = explode("-", $imageCode['styleCode']);
                $code = $parts[1];
                $cleanedCode = preg_replace("/[^0-9]/", "", $code);
                $image_code1 = $imageCode['styleCode'];
            }
            $imageUrl = "https://images.mauijim.com/sunglasses/{$cleanedCode}/{$image_code1}_side.jpg";
            $imageName = explode('sunglasses', $imageUrl)[1];

            $this->imageResponse($imageUrl, $headers, $cookies, $imageName, $headers, $cookies);
            $imageUrl = "https://images.mauijim.com/sunglasses/{$cleanedCode}/{$image_code1}_front.jpg";
            $imageName = explode('sunglasses', $imageUrl)[1];
            $this->imageResponse($imageUrl, $headers, $cookies, $imageName, $headers, $cookies);

            $imageUrl = "https://images.mauijim.com/sunglasses/{$cleanedCode}/{$image_code1}_quarter.jpg";
            $imageName = explode('sunglasses', $imageUrl)[1];
            $this->imageResponse($imageUrl, $headers, $cookies, $imageName, $imageCode['styleCode']);
        }
        $dataDict['productVariants'] = $productVariants;
        $this->dataList[] = $dataDict;
    }

    public function imageResponse($imageUrl, $headers, $cookies, $imageName, $imageCode)
    {
        $contextOptions = [
            'http' => [
                'header' => $headers,
                'cookie' => $cookies,
            ],
        ];
        $context = stream_context_create($contextOptions);
        $imageData = @file_get_contents($imageUrl, false, $context);
        if ($imageData === false) {
            $error = error_get_last();
            if ($error) {
                $cookieString = 'Language=en_US; Language=en_US; mauiJimB2BRememberMeStatusCookie=singleunit; mauijimb2bstorefrontRememberMe=bWpfdHJhZGVfam9lbCU0MG5hZGxhbnJlYWx0eS5jb206MTcwNDI4ODc3ODg0NDo5NjZhZDA5YWNlZGQ0OWJmZjk2ZDU5YzFjODZjZmFlMg; _hjSessionUser_320886=eyJpZCI6ImIxNTg2NjFkLTQ2ODEtNWY1Ny1iNGUzLWM5NDcxZjk0MGNkZSIsImNyZWF0ZWQiOjE3MDMwNDYyMDQ1MzEsImV4aXN0aW5nIjp0cnVlfQ==; ROUTE=.accstorefront-84659445b6-qgh8r; bm_mi=7E7FA8598C8BA6C03C91ADC0E0E71807~YAAQnLD3SF6mm2mMAQAAxoFthxadS0Rh9egFa7pSmj9c7SQKc0GnmAc8Rfl4xsb3vPt+Pl5NgclirtFgzdbcdPkYVxsLl1Omf5fkQajqgbNbBnr1Mk2lDaslUGysu9s+dbtYmkd/J4ZVbesIGzxIXEzsQe8NRYErz/B/kFHevCKxBg6Ik7TT0uMaEaMzTUZTjM/60tcVSCnsCJzTrwYEsCENd9FcvXnoPhpxhLK9EphoBCoegNt58pud19PA078DvEAZz/uT9fzeDMAPjr0O/pBiTJw/CwJZLqNz+rnnpcGScW6urroLEx15+bebYH0xM8oJRcszvaIU3lDiR63O9OkqaSC0n4DF/4hOYl+z2w==~1; _hjIncludedInSessionSample_320886=1; _hjAbsoluteSessionInProgress=0; _hjSession_320886=eyJpZCI6IjY5MzQ0ZTliLTRhYmUtNDlkMi1iNzhjLWFlMDM0NDY0OTU5ZCIsImMiOjE3MDMwNzkxNjAxNzEsInMiOjEsInIiOjEsInNiIjoxfQ==; ak_bmsc=B2D3943E881602AFE2FAF38907123C8D~000000000000000000000000000000~YAAQnLD3SEuzm2mMAQAACPFthxZNbEPwYwEFNPuSobMnkPbZkP2oy4/ohIWFlesqZjBQ49yKv0mhL1KcOz2+5pMZzONQ+D5pBBfRDWlC/Nz7PA5KfdyO5AtSdWD4FyNrY5G5KkP5dh8P2dwIdLg0CT3+ez4PzJ0IFB5Ob4y7RKmG0/ZI2z86RU2d+H6IaI+jqkV7/WkUDANUOa/yVwMUh+4BPRMwaeKRYnGtTdP3U0FlawlYYKBdNviZnZbVHByKLurRppF/5fpYEyjISSKncxz08zzA4w3YDAVZOrxml8OvN8+sO7tnqcSW+kjgwzrV8GiwRSH8CjLpt3aQX/BDrRSfzDwbEZMaDmsSqjpc4RNXiEIgpefsSDU7tSHuX6CiDm8LH/CvJAWhXcCeDIUgvlB8JRg144nMEa4ah7B8XT6xKLaB4IZhsZpebJOWz1gIi1m06Gt6XQ8H9hzAYnVNWBrQEBiQIKUar8YzQ8A4lqhV/sWlziZeD1t4KlONaub9j/Dtp2BxF5K+x2QKVEacORglBZWJeF+r6c1L1u1c3MWRTxlppDXOAA==; acceleratorSecureGUID=df36ee58eae7e4bbbdaf9ff0ce3ffa4d95710d2b; bm_sv=1BC9708413BDE07A78BB349C5CE6CA54~YAAQPgVaaAq4AFiMAQAAjJBwhxZU4akoB98qezr4Cec1Vq77bjROPXyUb2+2dQKrk5tLiJlinCoUkfh/pf8LIb7rJKsled7CXEiC0XyiX8ujzN5FCn5KSGcLK5ZM33UilDtit5I5H1TqDOkBaypB0rbkiD0uSRhyAyHshIojiC7rRTCsT1U3A6+/1EQeGtE9guS9tx0oZrc1n58UY9jdopmtHTdOYud50RMmdzik+mQBnEW6ZqmM1kbjIoxAf5nXufA=~1; mauijimb2bstorefrontRememberMe=bWpfdHJhZGVfam9lbCU0MG5hZGxhbnJlYWx0eS5jb206MTcwNDI4OTY5OTI5NzphNjM2MzUzOWE0N2E2YTkyZDEzMWY3Yjc4ZDM1MWI4Yg; acceleratorSecureGUID=cac7891552e5c24f922721ab59c15fa767ed7d4d';
                $newCookies = $this->getCookie();
                $cookiesStr = implode('; ', $newCookies);

                $combinedCookiesString = $cookieString . '; ' . implode('; ', $newCookies);

                $headers = $this->getHeader($combinedCookiesString);
                $img_code_slt = explode("/", $imageCode);
                $img_code = end($img_code_slt);
                $code_parts = explode("-", $img_code);
                $code = $code_parts[0];
                $cleaned_code = preg_replace('/^\D*/', '', $code);
                $image_code1 = $img_code;
                if (strpos($imageUrl, '_quarter') !== false) {
                    $image_Url = "https://images.mauijim.com/sunglasses/{$cleaned_code}/{$img_code}_quarter.jpg";
                    $this->imageResponse($image_Url, $headers, $newCookies, $imageName, $img_code);
                }
                if (strpos($imageUrl, '_front') !== false) {
                    $image_Url = "https://images.mauijim.com/sunglasses/{$cleaned_code}/{$img_code}_front.jpg";
                    $this->imageResponse($image_Url, $headers, $newCookies, $imageName, $img_code);
                }
                if (strpos($imageUrl, '_side') !== false) {
                    $image_Url = "https://images.mauijim.com/sunglasses/{$cleaned_code}/{$img_code}_side.jpg";
                    $this->imageResponse($image_Url, $headers, $newCookies, $imageName, $img_code);
                }
            }
        } else {
            $imageData = file_get_contents($imageUrl);

            $imageFilename = "imageOutput{$imageName}";
            if (!file_exists(dirname($imageFilename))) {
                mkdir(dirname($imageFilename), 0755, true);
            }
            file_put_contents($imageFilename, $imageData);


        }

    }
    public function saveDataToFile()
    {
        file_put_contents("mauiJimOutput.json", json_encode($this->dataList));
        $jsonString = file_get_contents('mauiJimOutput.json');
        $data = json_decode($jsonString, true);
        $servername = "localhost:3306";
        $username = "root";
        $password = "";
        $database = "ezcontact_x_datacenter";
        $databaseHandler = new DatabaseHandler($servername, $username, $password, $database);
        $databaseHandler->createTable();
        $databaseHandler->insertData($data);
        $databaseHandler->closeConnection();
    }
}



// run command = php .\index.php