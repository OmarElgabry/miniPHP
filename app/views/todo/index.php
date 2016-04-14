<div class="todo_container">

<h2>TODO Application</h2>

<!-- in case of normal post request  -->
<form action= "<?= PUBLIC_ROOT . "Todo/create" ?>"  method="post">
    <label>Content <span class="text-danger">*</span></label>
    <textarea name="content" class="form-control" required placeholder="What are you thinking?"></textarea>
    <input type='hidden' name = "csrf_token" value = "<?= Session::generateCsrfToken(); ?>">
    <button type="submit" name="submit" value="submit" class="btn btn-success">Create</button>
</form>


<!-- in case of ajax request  
<form action= "#" id="form-create-todo" method="post">
    <label>Content <span class="text-danger">*</span></label>
    <textarea name="content" class="form-control" required placeholder="What are you thinking?"></textarea>
    <button type="submit" name="submit" value="submit" class="btn btn-success">Create</button>
</form>
-->

<br>
<?php 

// display success or error messages in session
if(!empty(Session::get('success'))){
    echo $this->renderSuccess(Session::getAndDestroy('success'));
}else if(!empty(Session::get('errors'))){
    echo $this->renderErrors(Session::getAndDestroy('errors'));
}

?>

<br><hr><br>

<ul id="todo-list">
<?php 
    $todoData = $this->controller->todo->getAll();
    foreach($todoData as $todo){ 
?>
        <li>
            <p> <?= $this->autoLinks($this->encodeHTMLWithBR($todo["content"])); ?></p>

            <!-- in case of normal post request -->
            <form action= "<?= PUBLIC_ROOT . "Todo/delete" ?>" method="post">
                <input type='hidden' name= "todo_id" value="<?= "todo-" . Encryption::encryptId($todo["id"]);?>">
                <input type='hidden' name = "csrf_token" value = "<?= Session::generateCsrfToken(); ?>">
                <button type="submit" name="submit" value="submit" class="btn btn-xs btn-danger">Delete</button>
            </form>


            <!-- in case of ajax request 
            <form class="form-delete-todo" action= "#"  method="post">
                <input type='hidden' name= "todo_id" value="<?= "todo-" . Encryption::encryptId($todo["id"]);?>">
                <button type="submit" name="submit" value="submit" class="btn btn-xs btn-danger">Delete</button>
            </form>
             -->
        </li>
    <?php } ?>
</ul>

</div>