<h5>Sample Request:</h5>

<div class="wrapper">
    <ul>
    <?php foreach ($sampleRequests as $method => $methodSchema): ?>
        <?php foreach ($methodSchema as $statusCode => $request): ?>
            <span class="row"><?php echo $request; ?></span>
        <?php endforeach; ?>
    <?php endforeach; ?>
    </ul>
</div>
