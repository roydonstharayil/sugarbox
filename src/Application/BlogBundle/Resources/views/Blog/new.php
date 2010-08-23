

<?php $view->extend('BlogBundle::layout') ?>

Hello <?php echo 'sucsess' ?>!
<div>
    <?php echo $form->renderFormTag('#') ?>
<div>
    <?php echo $form['title']->render() ?>
</div>
<div>
    <?php echo $form['description']->render() ?>
</div>
<input type="submit" class="button" name="register" value="Submit" />
    </form></div>
