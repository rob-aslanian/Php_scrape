<?php 
   use CustomDB\MySQLDB as DB;
   use Parser\RBKParser;
   
   require_once('./db/init.php');
   require_once('./classes/parser.php');

   $id = $_GET["id"];

   $db_intsance = DB::getInstance();
   $post = $db_intsance->getByID($id);

   if($post) {
       if($post->description == null) {
           $parser = new RBKParser(); 
           $parser->updatePost($post->origin_url , $id);
           $post = $db_intsance->getByID($id);
        }
    }

    $title = $post ? $post->title : "Post not found";

?>

<?php include('components/head.php') ?>
    <? if($post): ?>
    <div class="container">
        <div class="row">
            <div class="col-lg-10">
                <h1 class="mt-4">
                    <?php echo $title ?>
                </h1>
                <p>Created at 
                    <?php 
                        $phpdate = strtotime( $post->created_at );
                        $date = date( 'M, Y, g:i a', $phpdate );

                        echo $date;
                    ?>
                </p>
                <hr>
                <? if($post->img_url != null): ?>
                    <img class="img-fluid rounded" src="<?php echo $post->img_url?>" alt="Image">
                    <hr>
                <? endif; ?>
                
                <?php echo $post->description ?>

                <hr>
            </div>

        </div>
    </div>
    <? else: ?>
        <?php include('components/page_not_found.php')?>
    <? endif; ?>
<?php include('components/footer.php') ?>