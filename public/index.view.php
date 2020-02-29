<html>
    <head>
        <link rel="stylesheet" type="text/css" href="bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="style.css">
        <script src="bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <div class="wrapper">

            <!-- Sidebar -->
            <nav id="sidebar">
                <div class="sidebar-header">
                    <h3>Bootstrap Sidebar</h3>
                </div>

                <ul class="list-unstyled components">
                    <?php foreach ($endpoints as $key => $endpoint): ?>
                        <li><?php echo $endpoint->getClassName(); ?></li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <!-- Page Content -->
            <div id="content">
                <nav class="navbar navbar-expand-lg navbar-light bg-light">
                    <div class="container-fluid">
                        <ul>
                            <?php foreach ($endpoints as $key => $endpoint): ?>
                                <li>
                                    <?php echo $endpoint->getClassName(); ?> Group
                                    <hr />
                                    Endpoint Uri: <?php echo $endpoint->getEndpoint(); ?>
                                    <?php getPartial('defaultHeaders', [
                                        'defaultHeaders' => $endpoint->getDefaultHeaders()
                                    ]); ?>
                                    <?php getPartial('queryParams', [
                                        'queryParams' => $endpoint->getRequestQueryParams()
                                    ]); ?>
                                    <?php getPartial('sampleRequest', [
                                        'sampleRequests' => $endpoint->getSampleRequests()
                                    ]); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </nav>
            </div>

        </div>
    </body>
</html>
