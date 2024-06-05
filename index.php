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
    <header>
        <div class="header__title">Computer shop</div>
        <div class="header__right__part">

            <div class="header__btn__container">
                <button id="theme_switcher">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(126,94,255,0.78);transform: ;msFilter:;"><path d="M12 11.807A9.002 9.002 0 0 1 10.049 2a9.942 9.942 0 0 0-5.12 2.735c-3.905 3.905-3.905 10.237 0 14.142 3.906 3.906 10.237 3.905 14.143 0a9.946 9.946 0 0 0 2.735-5.119A9.003 9.003 0 0 1 12 11.807z"></path></svg>
                </button>
                <button id="items_cart">Cart</button>
                <button id="sign_inBtn" class="header__btn">Sign In</button>
                <button id="sign_upBtn" class="header__btn">Sign Up</button>
                <button id="sign_outBtn" class="header__btn">Sign Out</button>
            </div>
        </div>
    </header>

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

    <div class="container">
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