<h5>Request Query Params:</h5>

<div class="wrapper">
    <ul>
        <?php foreach ($queryParams as $param => $value): ?>
            <span class="row">
                <?php echo $param; ?> (<?php echo $value['type']; ?>): <?php echo $value['description'] ?>        
            </span>
        <?php endforeach; ?>
    </ul>
</div>
