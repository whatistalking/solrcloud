<div class="navbar navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand" href="index.php">Search Cloud</a>
            <div class="nav-collapse">
            <ul class="nav">
                <li>
                <a href="report.php">Report</a>
                </li>
                <li>
                <a href="service.php">Service</a>
                </li>
                <li>
                <a href="instance.php">Instance</a>
                </li>
                <li>
                <a href="host.php">Host</a>
                </li>
                <li>
                <a href="queue.php">Queue</a>
                </li>
            </ul>
            <ul class="nav pull-right">
                <?php if($uname) { ?>
                <li>
                    <a><?php echo $uname;?></a>
                </li>
                <?php } ?>
                <li>
                <a href="alert.php">Alert Setting</a>
                </li>
            </ul>
            </div>
        </div>
    </div>
</div>
