<footer>
    <div class='footer'>
        <hr />
        <?php
        printf("<span class='RCS'>\$ Id: %s$</span>", GITHASH);
        printf("<span class='RCS'>Session expires at %s</span>", date("Y-m-d H:i:s", $_SESSION['expires']));
        ?>
    </div>
</footer>