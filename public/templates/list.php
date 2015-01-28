<div id="glossaire">
  <div class="alphabet">
    <?php foreach(range('A','Z') as $lettre): ?>
      <?php
      $class = "active";
      if (!empty ( $_GET["lettre"] ) ) { //Si il a choisit une lettre, on insère la lettre chosie
        if($lettre == $_GET["lettre"]):
          $class = "active";
        else:
          $class = "";
        endif;
      } elseif ($lettre != "A") {
        $class = "";
      }?>
      <a href='?lettre=<?php echo $lettre ?>' <?php if(!empty($class)): echo "class='$class'"; endif;?>><?php echo $lettre ?></a> <!--Lettres de l'alphabet !-->
    <?php endforeach;?>
  </div>
  <ul>
    <?php
    $loop = new WP_Query( array( 'post_type' => 'lex_word', 'posts_per_page' => -1, 'orderby' => "title", 'order'=>"asc") );
    if (!empty ( $_GET["lettre"] ) ) { //Si il a choisit une lettre, on insère la lettre chosiie
      $lettre=ucwords( $_GET["lettre"] );
    } else {    //Sinon on insère "A" par défaut
      $lettre="A";
    }
    while ( $loop->have_posts() ) : $loop->the_post();
      // Fix for capitalized words
      $title=ucwords( strtolower( get_the_title() ) ); // On met les premières lettres en majuscule

      //$title = get_the_title();
      if ( substr($title, 0, 1) == $lettre ):
        ?>    <li class="glossaire_preview">
        <strong><a name="<?php echo $title?>" id="<?php echo $title?>"><?php echo $title;?></a> : </strong> <?php _e(get_the_content());  ?>
      </li>

      <?php   endif;
    endwhile;
    ?>
  </ul>
</div>
