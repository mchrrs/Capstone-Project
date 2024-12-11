<!-- header section starts  -->

<header class="header">

    <nav class="navbar nav-1">
        <section class="flex">
            <a href="home.php" class="logo"><i class="fas fa-house"></i>AMS</a>
        </section>
    </nav>

    <nav class="navbar nav-2">
        <section class="flex">
            <div id="menu-btn" class="fas fa-bars"></div>
            <ul>
                <li><a href="#">Account <i class="fas fa-angle-down"></i></a>
                    <ul>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <?php if ($user_id != '') { ?>
                            <li><a href="/components/user_logout.php" onclick="return confirm('logout from this website?');">Logout</a></li>
                        <?php } ?>
                    </ul>
                </li>
            </ul>
        </section>
    </nav>

</header>

<!-- header section ends -->
