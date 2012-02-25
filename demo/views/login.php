<?php if(isset($error)): ?>
<div style="color: red">
	<?php echo $error; ?>
</div>
<?php endif; ?>

<?php echo form_open(); ?>

<label for="username">Username: </label>
<input type="text" name="username" id="username" />

<label for="password">Password: </label>
<input type="password" name="password" id="password" />

<label for="remember">Remember me: </label>
<input type="checkbox" name="remember" value="1" id="remember" />

<input type="submit" value="login" />

</form>