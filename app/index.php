<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Computer shop</title>
    <link rel="stylesheet" href="styles/style.css">
</head>

<body>
    <?php include 'pages/header.php'; ?>

    <div id='container' class="container">
        <div id="sidebar__container" class="sidebar__container">
            <div class="sidebar__part">
                <div class="sidebar__title">Categories</div>
                <div id="cpu__category__div" class="sidebar__item">CPU</div>
                <div id="gpu__category__div" class="sidebar__item">GPU</div>
                <div id="motherboards__category__div" class="sidebar__item">Motherboards</div>
                <div id="ram__category__div" class="sidebar__item">RAM</div>
            </div>

            <div class="sidebar__part sidebar__sorting">
                <div class="sidebar__title">Sort</div>
                <div>
                    <input id="asc__radio" name="sort_items" type="radio" >
                    <label for="asc__radio">from cheap to expense</label>
                </div>
                <div>
                    <input id="desc__radio" name="sort_items" type="radio" >
                    <label for="desc__radio">from expense to cheap</label>
                </div>
            </div>
        </div>
        <div id="items__list"></div>
    </div>

    <script src="scripts/functions.js"></script>
    <script src="scripts/theme.js"></script>
    <script src="scripts/main.js"></script>
    <script src="scripts/users.js"></script>
    <script src="scripts/catalog.js"></script>
</body>
</html>