<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class POSController extends Controller
{
    public function index()
    {
        return Inertia::render('POS/Terminal', [
            'categories' => $this->getCategories(),
            'modifiers' => $this->getModifiers(),
            'serverName' => 'Wolfgang Z.',
            'tableNumber' => null,
        ]);
    }

    private function getCategories(): array
    {
        return [
            [
                'name' => 'Appetizers',
                'icon' => 'appetizer',
                'items' => [
                    ['name' => 'Cured Meats', 'price' => 28.00, 'color' => 'red'],
                    ['name' => 'Cheese Board', 'price' => 26.00, 'color' => 'green'],
                    ['name' => 'Burrata', 'price' => 22.00, 'color' => 'green'],
                    ['name' => 'Bruschetta', 'price' => 18.00, 'color' => 'green'],
                    ['name' => 'Focaccia Ricotta', 'price' => 16.00, 'color' => 'blue'],
                    ['name' => 'Shrimp Cocktail', 'price' => 28.00, 'color' => 'red'],
                    ['name' => 'Lobster Cocktail', 'price' => 38.00, 'color' => 'red'],
                    ['name' => 'Fried Calamari', 'price' => 24.00, 'color' => 'gold'],
                    ['name' => 'Octopus', 'price' => 26.00, 'color' => 'green'],
                    ['name' => 'Croquettes', 'price' => 18.00, 'color' => 'green'],
                    ['name' => 'Tuna Tartare', 'price' => 26.00, 'color' => 'red'],
                    ['name' => 'Oysters (6)', 'price' => 24.00, 'color' => 'blue'],
                    ['name' => 'Oysters (12)', 'price' => 44.00, 'color' => 'blue'],
                    ['name' => 'Wagyu Carpaccio', 'price' => 32.00, 'color' => 'red'],
                    ['name' => 'Prosciutto & Melon', 'price' => 22.00, 'color' => 'green'],
                ],
            ],
            [
                'name' => 'Steaks',
                'icon' => 'steak',
                'items' => [
                    ['name' => 'Porterhouse (2)', 'price' => 125.00, 'color' => 'burgundy'],
                    ['name' => 'Porterhouse (3)', 'price' => 185.00, 'color' => 'burgundy'],
                    ['name' => 'Porterhouse (4)', 'price' => 245.00, 'color' => 'burgundy'],
                    ['name' => 'Ribeye', 'price' => 68.00, 'color' => 'red'],
                    ['name' => 'NY Strip', 'price' => 62.00, 'color' => 'red'],
                    ['name' => 'Filet Mignon', 'price' => 65.00, 'color' => 'red'],
                    ['name' => 'Bone-In Ribeye', 'price' => 78.00, 'color' => 'red'],
                    ['name' => 'Tomahawk', 'price' => 135.00, 'color' => 'burgundy'],
                    ['name' => 'Wagyu A5 Strip', 'price' => 175.00, 'color' => 'burgundy'],
                    ['name' => 'Veal Chop', 'price' => 62.00, 'color' => 'red'],
                    ['name' => 'Lamb Chops', 'price' => 58.00, 'color' => 'red'],
                    ['name' => 'Kobe Burger', 'price' => 28.00, 'color' => 'gold'],
                ],
            ],
            [
                'name' => 'Seafood',
                'icon' => 'fish',
                'items' => [
                    ['name' => 'Whole Branzino', 'price' => 48.00, 'color' => 'blue'],
                    ['name' => 'Salmon', 'price' => 42.00, 'color' => 'blue'],
                    ['name' => 'Chilean Sea Bass', 'price' => 58.00, 'color' => 'blue'],
                    ['name' => 'Lobster Tail', 'price' => 68.00, 'color' => 'red'],
                    ['name' => 'King Crab Legs', 'price' => 85.00, 'color' => 'red'],
                    ['name' => 'Jumbo Lump Crab', 'price' => 38.00, 'color' => 'blue'],
                    ['name' => 'Seafood Tower', 'price' => 145.00, 'color' => 'burgundy'],
                    ['name' => 'Swordfish', 'price' => 44.00, 'color' => 'blue'],
                    ['name' => 'Shrimp Scampi', 'price' => 38.00, 'color' => 'gold'],
                    ['name' => 'Squid Ink Pasta', 'price' => 32.00, 'color' => 'green'],
                ],
            ],
            [
                'name' => 'Salads',
                'icon' => 'salad',
                'items' => [
                    ['name' => 'Caesar Salad', 'price' => 18.00, 'color' => 'green'],
                    ['name' => 'Wolfgang Salad', 'price' => 20.00, 'color' => 'green'],
                    ['name' => 'Kale Salad', 'price' => 18.00, 'color' => 'green'],
                    ['name' => 'Little Gem', 'price' => 18.00, 'color' => 'green'],
                    ['name' => 'Squash Salad', 'price' => 20.00, 'color' => 'green'],
                    ['name' => 'Market Greens', 'price' => 16.00, 'color' => 'green'],
                    ['name' => 'Wedge Salad', 'price' => 18.00, 'color' => 'green'],
                    ['name' => 'Tomato & Onion', 'price' => 18.00, 'color' => 'green'],
                ],
            ],
            [
                'name' => 'Pasta',
                'icon' => 'pasta',
                'items' => [
                    ['name' => 'Cacio e Pepe', 'price' => 28.00, 'color' => 'gold'],
                    ['name' => 'Lasagna', 'price' => 30.00, 'color' => 'red'],
                    ['name' => 'Ravioli', 'price' => 28.00, 'color' => 'gold'],
                    ['name' => 'Squid Ink Spaghetti', 'price' => 30.00, 'color' => 'green'],
                    ['name' => 'Orecchiette', 'price' => 26.00, 'color' => 'green'],
                    ['name' => 'Paccheri', 'price' => 28.00, 'color' => 'green'],
                    ['name' => 'Penne Vodka', 'price' => 26.00, 'color' => 'red'],
                    ['name' => 'Lobster Risotto', 'price' => 38.00, 'color' => 'burgundy'],
                    ['name' => 'Truffle Gnocchi', 'price' => 32.00, 'color' => 'gold'],
                ],
            ],
            [
                'name' => 'Sides',
                'icon' => 'side',
                'items' => [
                    ['name' => 'Creamed Spinach', 'price' => 16.00, 'color' => 'green'],
                    ['name' => 'German Potatoes', 'price' => 16.00, 'color' => 'gold'],
                    ['name' => 'Mashed Potatoes', 'price' => 16.00, 'color' => 'gold'],
                    ['name' => 'Truffle Fries', 'price' => 18.00, 'color' => 'gold'],
                    ['name' => 'Regular Fries', 'price' => 14.00, 'color' => 'gold'],
                    ['name' => 'Broccoli Rabe', 'price' => 16.00, 'color' => 'green'],
                    ['name' => 'Asparagus', 'price' => 18.00, 'color' => 'green'],
                    ['name' => 'Mushrooms', 'price' => 16.00, 'color' => 'green'],
                    ['name' => 'Brussels Sprouts', 'price' => 16.00, 'color' => 'green'],
                    ['name' => 'Onion Rings', 'price' => 16.00, 'color' => 'gold'],
                    ['name' => 'Baked Potato', 'price' => 14.00, 'color' => 'gold'],
                    ['name' => 'Mac & Cheese', 'price' => 18.00, 'color' => 'gold'],
                    ['name' => 'Sautéed Spinach', 'price' => 14.00, 'color' => 'green'],
                    ['name' => 'Extra Croquette', 'price' => 8.00, 'color' => 'gold'],
                ],
            ],
            [
                'name' => 'Wine by Glass',
                'icon' => 'wine',
                'items' => [
                    ['name' => 'Pinot Grigio', 'price' => 16.00, 'color' => 'gold'],
                    ['name' => 'Sauvignon Blanc', 'price' => 18.00, 'color' => 'gold'],
                    ['name' => 'Chardonnay', 'price' => 18.00, 'color' => 'gold'],
                    ['name' => 'Rosé', 'price' => 16.00, 'color' => 'rose'],
                    ['name' => 'Pinot Noir', 'price' => 18.00, 'color' => 'burgundy'],
                    ['name' => 'Cabernet Sauv.', 'price' => 22.00, 'color' => 'burgundy'],
                    ['name' => 'Malbec', 'price' => 18.00, 'color' => 'burgundy'],
                    ['name' => 'Barolo', 'price' => 28.00, 'color' => 'burgundy'],
                    ['name' => 'Prosecco', 'price' => 16.00, 'color' => 'gold'],
                    ['name' => 'Champagne', 'price' => 28.00, 'color' => 'gold'],
                ],
            ],
            [
                'name' => 'Cocktails',
                'icon' => 'cocktail',
                'items' => [
                    ['name' => 'Classic Martini', 'price' => 22.00, 'color' => 'blue'],
                    ['name' => 'Manhattan', 'price' => 22.00, 'color' => 'burgundy'],
                    ['name' => 'Old Fashioned', 'price' => 22.00, 'color' => 'gold'],
                    ['name' => 'Negroni', 'price' => 20.00, 'color' => 'red'],
                    ['name' => 'Espresso Martini', 'price' => 22.00, 'color' => 'burgundy'],
                    ['name' => 'Aperol Spritz', 'price' => 18.00, 'color' => 'orange'],
                    ['name' => 'Margarita', 'price' => 18.00, 'color' => 'green'],
                    ['name' => 'Whiskey Sour', 'price' => 20.00, 'color' => 'gold'],
                    ['name' => 'Tom Collins', 'price' => 18.00, 'color' => 'blue'],
                    ['name' => 'Cosmopolitan', 'price' => 20.00, 'color' => 'red'],
                ],
            ],
            [
                'name' => 'Beer',
                'icon' => 'beer',
                'items' => [
                    ['name' => 'Stella Artois', 'price' => 10.00, 'color' => 'gold'],
                    ['name' => 'Heineken', 'price' => 10.00, 'color' => 'green'],
                    ['name' => 'Peroni', 'price' => 10.00, 'color' => 'blue'],
                    ['name' => 'Corona', 'price' => 10.00, 'color' => 'gold'],
                    ['name' => 'IPA Draft', 'price' => 12.00, 'color' => 'gold'],
                    ['name' => 'Lager Draft', 'price' => 10.00, 'color' => 'gold'],
                    ['name' => 'Guinness', 'price' => 12.00, 'color' => 'burgundy'],
                    ['name' => 'N/A Beer', 'price' => 8.00, 'color' => 'green'],
                ],
            ],
            [
                'name' => 'Desserts',
                'icon' => 'dessert',
                'items' => [
                    ['name' => 'Tiramisu', 'price' => 18.00, 'color' => 'gold'],
                    ['name' => 'Cheesecake', 'price' => 18.00, 'color' => 'gold'],
                    ['name' => 'Crème Brûlée', 'price' => 16.00, 'color' => 'gold'],
                    ['name' => 'Chocolate Cake', 'price' => 18.00, 'color' => 'burgundy'],
                    ['name' => 'Panna Cotta', 'price' => 16.00, 'color' => 'gold'],
                    ['name' => 'Affogato', 'price' => 14.00, 'color' => 'burgundy'],
                    ['name' => 'Gelato', 'price' => 12.00, 'color' => 'blue'],
                    ['name' => 'Sorbet', 'price' => 12.00, 'color' => 'green'],
                ],
            ],
            [
                'name' => 'Beverages',
                'icon' => 'beverage',
                'items' => [
                    ['name' => 'Espresso', 'price' => 6.00, 'color' => 'burgundy'],
                    ['name' => 'Cappuccino', 'price' => 8.00, 'color' => 'gold'],
                    ['name' => 'Coffee', 'price' => 5.00, 'color' => 'burgundy'],
                    ['name' => 'Tea', 'price' => 5.00, 'color' => 'green'],
                    ['name' => 'Still Water', 'price' => 4.00, 'color' => 'blue'],
                    ['name' => 'Sparkling Water', 'price' => 4.00, 'color' => 'blue'],
                    ['name' => 'Soft Drink', 'price' => 5.00, 'color' => 'blue'],
                    ['name' => 'Juice', 'price' => 6.00, 'color' => 'orange'],
                    ['name' => 'Lemonade', 'price' => 6.00, 'color' => 'gold'],
                    ['name' => 'Iced Tea', 'price' => 6.00, 'color' => 'gold'],
                ],
            ],
            [
                'name' => 'Happy Hour',
                'icon' => 'happy',
                'items' => [
                    ['name' => 'HH Burrata', 'price' => 12.00, 'color' => 'green'],
                    ['name' => 'HH Croquette', 'price' => 8.00, 'color' => 'green'],
                    ['name' => 'HH Bruschetta', 'price' => 10.00, 'color' => 'green'],
                    ['name' => 'HH Calamari', 'price' => 12.00, 'color' => 'green'],
                    ['name' => 'HH Prosciutto', 'price' => 12.00, 'color' => 'green'],
                    ['name' => 'HH Focaccia', 'price' => 8.00, 'color' => 'green'],
                    ['name' => 'HH Cheese', 'price' => 10.00, 'color' => 'green'],
                    ['name' => 'HH Wine Glass', 'price' => 10.00, 'color' => 'gold'],
                    ['name' => 'HH Beer', 'price' => 7.00, 'color' => 'gold'],
                    ['name' => 'HH Cocktail', 'price' => 12.00, 'color' => 'gold'],
                ],
            ],
        ];
    }

    private function getModifiers(): array
    {
        return [
            'temps' => [
                ['name' => 'Rare', 'abbr' => 'R'],
                ['name' => 'Medium Rare', 'abbr' => 'MR'],
                ['name' => 'Medium', 'abbr' => 'M'],
                ['name' => 'Medium Well', 'abbr' => 'MW'],
                ['name' => 'Well Done', 'abbr' => 'WD'],
            ],
            'prep' => [
                ['name' => 'No Salt'],
                ['name' => 'Extra Sauce'],
                ['name' => 'On the Side'],
                ['name' => 'No Onion'],
                ['name' => 'No Garlic'],
                ['name' => 'Gluten Free'],
                ['name' => 'Allergy Alert'],
                ['name' => 'Extra Crispy'],
                ['name' => 'Light Oil'],
                ['name' => 'Dressing OTS'],
            ],
            'addons' => [
                ['name' => 'Add Bacon', 'price' => 6.00],
                ['name' => 'Add Truffle', 'price' => 12.00],
                ['name' => 'Add Lobster', 'price' => 25.00],
                ['name' => 'Add Shrimp', 'price' => 14.00],
                ['name' => 'Add Cheese', 'price' => 4.00],
                ['name' => 'Extra Bread', 'price' => 0.00],
            ],
        ];
    }
}
