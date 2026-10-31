<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Home | NSBM NewsDesk</title>
    </head>
    <body>
        <header>
            <div class="bar">
                <a href="index.php"><img class="logo" src="assets/logo.jpg" alt="NSBM"></a>
                <a class="brand" href="index.php">NewsDesk</a>

                <nav>
                    <a href="index.php" class="on">Home</a>
                    <a href="categories.php?name=Academic">Academic</a>
                    <a href="categories.php?name=Sports">Sports / Clubs</a>
                    <a href="categories.php?name=Events">Events</a>
                    <a href="categories.php?name=General">General</a>
                </nav>

                <form class="search-form" action="search.php" method="get">
                    <input class="search" name="q" placeholder="Search news">
                </form>

                <a class="btn outline small" href="auth/login.php">Log in / Register</a>
                <a class="avatar-btn guest" href="auth/login.php" title="Log in">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor" aria-hidden="true">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8z"></path>
                    </svg>
                </a>
            </div>
        </header>
    </body>





</html>