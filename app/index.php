<?php 
   use CustomDB\MySQLDB as DB;
   use Parser\RBKParser;
   
   require_once('./db/init.php');
   require_once('./classes/parser.php');

   $title = "Main Page";
   $parser = new RBKParser();
   $parser->setPostsToDB();

   $db_intsance = DB::getInstance();
   $posts = $db_intsance->getAll();
?>

<?php include('./components/head.php')?>
    <?php if( count($posts) > 0 ): ?>
    <div class="jumbotron">
        <div class="container">
            <div class="row">
                <?php foreach($posts as $post):?>
                    <div class="col-md-6 mt-3">
                        <div class="card" >
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo $post->title ?>
                                </h5>
                                <a href="<?php echo $post->origin_url ?>" class="card-link"
                                   target="_blank">
                                    Original link
                                </a>
                                <?php if(substr($post->origin_url, 0, 5) === "https"): ?>
                                <a href="/detail.php?id=<?php echo $post->id ?>" class="card-link">
                                    Read more
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach;?>
                
            </div>
        </div>
    </div>
    <?php endif; ?>

<? include('./components/footer.php') ?>