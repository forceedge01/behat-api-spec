<h5>Response Schema:</h5>

<div class="wrapper row">
    <div class="responses row">
    <?php foreach ($responses as $requestType => $response): ?>
        <hr />
        <div class="request w-100">
            <?php foreach ($response as $statusCode => $schema): ?>
                <div class="response w-100">
                    <div class="row"><?php echo $requestType; ?>::<?php echo $statusCode; ?></div>
                    <div class="row">Headers:</div>
                    <div class="row code php"><?php echo formatCode($schema['headers']); ?></div>
                    <div class="row">Body:</div>
                    <div class="row code php"><?php echo formatCode($schema['body']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>
