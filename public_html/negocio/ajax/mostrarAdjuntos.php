<table class="table">
    <?php
    foreach ($_FILES as $f):
        ?>
        <tr>
            <td><?php echo $f['name']; ?></td>
        </tr>
        <?php
    endforeach;
    unset($_FILES);
    ?>
</table>