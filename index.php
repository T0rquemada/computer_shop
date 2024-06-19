<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Computer shop</title>
    <link rel="stylesheet" href="static/styles/style.css">
</head>

<body>
    <?php include 'static/pages/header.php'; ?>

    <div id="pop_up__screen">
        <div id="pop_up__container">
            <div class="pop_up__header">
                <div id="pop_up__title"></div>
                <div id="pop_up__close">X</div>
            </div>

            <div id="pop_up__footer" class="pop_up__footer">
                <button class="pop_up__btns" id="pop_up__cancel">Cancel</button>
                <button class="pop_up__btns" id="pop_up__submit">Submit</button>
            </div>
        </div>
    </div>

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

    <script src="static/scripts/main.js"></script>
    <script src="static/scripts/users.js"></script>
    <script src="static/scripts/catalog.js"></script>
</body>
</html>