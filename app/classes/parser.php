<?php 
    namespace Parser;
    use CustomDB;
    use DOMDocument;
    use DOMXPath;

    require_once('db/init.php');

    class RBKParser {
        private $rbk_url = "https://www.rbc.ru";
        private $db;

        public function __construct() {
            libxml_use_internal_errors(true);
            $this->$db = CustomDB\MySQLDB::getInstance();
        }

        public function scrape($url){
           
            $headers = [
                    "Accept: text/html",
                    "Cache-Control: max-age=0",
                    "Connection: keep-alive",
                    "Pragma: "
            ];

            $config = [
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_CAINFO => getcwd() . '\CAcert.pem',
                    CURLOPT_RETURNTRANSFER => TRUE ,
                    CURLOPT_FOLLOWLOCATION => TRUE ,
                    CURLOPT_AUTOREFERER => TRUE ,
                    CURLOPT_CONNECTTIMEOUT => 120 ,
                    CURLOPT_TIMEOUT => 100 ,
                    CURLOPT_MAXREDIRS => 10 ,                   
                    CURLOPT_USERAGENT => "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8" ,
                    CURLOPT_URL => $url
             ] ;
            $handle = curl_init() ;
            curl_setopt_array($handle,$config) ;
            curl_setopt($handle,CURLOPT_HTTPHEADER,$headers) ;
            $output = curl_exec($handle) ;
    
            if(curl_exec($handle) === false) {
                echo 'Curl error: ' . curl_error($handle);
            }
    
            curl_close($handle);
           
            return $this->_getDOM(mb_convert_encoding($output , 'utf-8'));
        }

        public function setPostsToDB() {
            $page = $this->scrape('https://www.rbc.ru');

            if(!$this->_checkIfAddNewPost($page)) {
                $posts = iterator_to_array($page->query('//div[@class="js-news-feed-list"]//a[@class="news-feed__item js-news-feed-item js-yandex-counter" and position() < 15]'));
                $posts_arr = array_reverse($posts);

                foreach($posts_arr as $post) {
                    $title = trim($post->childNodes[1]->nodeValue);
                    $url = $post->attributes->getNamedItem('href')->value;
                    $this->$db->insert([
                        "title" => $title,
                        "origin_url" => $url,
                    ]);
                }
            }

        }

        public function updatePost($url , $id) {
            $page = $this->scrape($url);
            $post = $page->query('//div[contains(@class , "article__text")]/p');
            $img =  $page->query('//div[@class="article__main-image__wrap"]/img');

            if(isset($post)) {
                $data = [ 
                    "id" => $id,
                    "img_url" => null,
                ];

                if(isset($img[0])) {
                    $data["img_url"] = $img[0]->attributes->getNamedItem('src')->value;
                }

                foreach($post as $p) {
                    if($p->nodeName == 'p') {
                        $data["description"] .= '<p>' . $p->textContent . '</p>';
                    }
                }

                $this->$db->update($data);   
            }
            
        }

        private function _checkIfAddNewPost($page) {
            $db_post = $this->$db->getFirst();
            $post = $page->query('//div[@class="js-news-feed-list"]//a[@class="news-feed__item js-news-feed-item js-yandex-counter"][1]');
            $post_url = $post[0]->attributes->getNamedItem('href')->value;

            return $db_post->origin_url == $post_url;

        }

        private function _getDOM($context) {
            $dom = new DOMDocument();
            $dom->loadHTML($context);

            return $this->_getDOMXPath($dom);
        }

        private function _getDOMXPath($e) {
            return new DOMXPath($e);
        }
        
    }

?>