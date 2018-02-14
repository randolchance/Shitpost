<div class="login">
    <h3>Please login to shitpost.</h3>
    <br><br>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        User: <input type="text" name="usr" value="<?php echo $usr;?>">
        <span class="error"> <?php echo $usrErr;?></span><br><br>

        Password: <input type="password" name="pwd">
        <span class="error"> <?php echo $pwdErr;?></span><br><br>

        <input class="submit" type="submit" name="submit" value="Enter">
    </form>
</div>