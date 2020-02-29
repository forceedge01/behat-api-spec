<h5>Default Request Headers:</h5>

<div class="wrapper">
    <ul>
        <?php foreach ($defaultHeaders as $header => $value): ?>
            <li><?php echo $header; ?>: <?php echo $value; ?></li>
        <?php endforeach; ?>
    </ul>
</div>
