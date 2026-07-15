<?php require_once '../../negocio/ajax/procesarCargarContratoEnSelect.php'; ?>
<div class="form-group">
    <label for="idContrato" class='col-sm-4 control-label'>Contrato: </label>
    <div class="col-sm-4">
        <select id="idContrato" name="idContrato" class="form-control">
            <?php
            if (isset($contratos)):
                if (count($contratos) > 0):
                    foreach ($contratos as $c) :
                        ?>
                        <option value="<?php echo $c->getIdContrato(); ?>"><?php echo $c->getContrato(); ?></option>
                        <?php
                    endforeach;
                else:
                    ?>
                    <option value="">No hay contratos asociados</option>
                <?php
                endif;
            endif;
            ?>
        </select>
    </div>

</div>