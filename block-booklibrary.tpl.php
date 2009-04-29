<div id="booklibrary-block">
  <b>test</b>
  <?php print_r($block->content) ?>
  <h3><?php $book->title;?></h3>
  <img alt="book cover" src="<?php $book->image_medium;?>">
  <a href="<?php $book->book_link;?>">meer</a>
</div>