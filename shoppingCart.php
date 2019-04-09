<?php

/**
 * set error checking to strict
 */
declare(strict_types=1);

/**
 * Class ProductItem
 */
class ProductItem
{
    private $name;
    private $price;
    const BASE_ADD_URL = "";

    /**
     * CartItem constructor.
     * @param $name
     * @param $price
     * @param $quantity
     */
    public function __construct($name, $price)
    {
        $this->name = $name;
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * All prices should be displayed to 2 decimal places
     *
     * @param int $decimal
     * @return string
     */
    public function getDisplayPrice($decimal = 2): string
    {
        return number_format((float)$this->price, $decimal, '.', '');
    }

    /**
     * @param bool $isEncode
     * @return string
     */
    public function getAddToCartLink($isEncode = false): string
    {
        $addUrl = self::BASE_ADD_URL . "?name=" . $this->getName();
        if ($isEncode) {
            return urlencode($addUrl);
        }
        return $addUrl;
    }

}

/**
 * Class CartItem
 */
class CartItem
{
    private $product;
    private $quantity;
    const BASE_REMOVE_URL = "";

    /**
     * CartItem constructor.
     * @param ProductItem $product
     * @param int $quantity
     */
    public function __construct(ProductItem $product, $quantity = 1)
    {
        $this->product = $product;
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->product->getName();
    }

    /**
     * @param int $decimal
     * @return string
     */
    public function getPrice($decimal = 2): string
    {
        $totalPrice = $this->product->getPrice() * $this->quantity;
        return number_format((float)$totalPrice, $decimal, '.', '');
    }

    /**
     * @return mixed
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * increase quantity
     *
     * @param $number
     */
    public function increaseQuantity($number)
    {
        $this->quantity += $number;
    }

    /**
     * decrease quantity
     *
     * @param $number
     */
    public function decreaseQuantity($number)
    {
        $this->quantity -= $number;
    }

    /**
     * get remove item link
     *
     * @param bool $isEncode
     * @return string
     */
    public function getRemoveItemLink($isEncode = false): string
    {
        $removeUrl = self::BASE_REMOVE_URL . "?name=" . $this->getName();
        if ($isEncode) {
            return urlencode($removeUrl);
        }
        return $removeUrl;
    }

}

/**
 * Class Products
 */
class Products
{
    /**
     * $productItems = [
     *      "product_name1" => productItem1,
     *      "product_name2" => productItem2,
     * ];
     *
     * @var array
     */
    private $productItems = [];

    public function __construct($products = null)
    {
        if (!empty($products)) {
            $this->updateProductList($products);
        }
    }

    /**
     * @param $products
     * @throws Exception
     */
    public function updateProductList($products)
    {
        if (!is_array($products) || empty($products[0]['name'])) {
            throw new Exception(' Parameter Error! ');
        }
        $this->productItems = [];
        foreach ($products as $item) {
            $this->productItems[$item['name']] = new ProductItem($item['name'], $item['price']);
        }
    }

    /**
     * add a product to the products list
     *
     * @param $name
     * @param $price
     */
    public function addProduct($name, $price)
    {
        if (empty($this->productItems[$name])) {
            $this->productItems[$name] = new ProductItem($name, $price);
        }
    }

    /**
     * Products should be listed in this format: product name, price, link to add product
     *
     * @return array
     */
    public function getProductList(): array
    {
        $products = [];
        foreach ($this->productItems as $item) {
            $products[] = [
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'add_link' => $item->getAddToCartLink(),
            ];
        }
        return $products;
    }

}

/**
 * Class ShoppingCart
 */
class ShoppingCart
{
    /**
     * $cartItems = [
     *      "product_name1" => CartItem1,
     *      "product_name2" => CartItem2,
     * ];
     * @var array
     */
    private $cartItemList = [];
    private $totalPrice = 0;
    private $totalQuantity = 0;

    public function __construct(Products $products = null)
    {
        if (!empty($products)) {
            $this->updateCartItems($products);
        }
    }

    /**
     * update during page loads / refreshes
     *
     * @param Products $products
     * @return null
     *
     */
    public function updateCartItems(Products $products)
    {
        foreach ($products->getProductList() as $item) {
            $this->totalQuantity += 1;
            $this->totalPrice += $item['price'];
            if (!empty($this->cartItemList[$item['name']])) {
                $this->cartItemList[$item['name']]->increaseQuantity(1);
            } else {
                $newProduct = new ProductItem($item['name'], $item['price']);
                $this->cartItemList[$item['name']] = new CartItem($newProduct, 1);
            }
        }
    }

    /**
     * Cart products should be listed in this format: product name, price, quantity, total, remove link
     *
     * @return array
     */
    public function getCartItemsList(): array
    {
        $cartProducts = [];
        foreach ($this->cartItemList as $name => $item) {
            $cartProducts[] = [
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'quantity' => $item->getQuantity(),
                'total' => $this->totalQuantity,
                'remove_link' => $item->getRemoveItemLink(),
            ];
        }
        return $cartProducts;
    }

    /**
     * @param $name
     * @return bool
     */
    private function _isCartItemExists($name): bool
    {
        return !empty($this->cartItemList[$name]);
    }

    /**
     * add a product to the cart
     * Adding an existing product will only update existing cart product quantity
     *
     * @param $name
     * @param $price
     */
    public function addCartItem($name, $price)
    {
        if ($this->_isCartItemExists($name)) {
            $this->cartItemList[$name]->increaseQuantity(1);
        } else {
            $this->cartItemList[$name] = new CartItem(new ProductItem($name, $price), 1);
        }
    }

    /**
     * remove a product from the cart
     *
     * @param $name
     */
    public function removeCartItem($name)
    {
        if ($this->_isCartItemExists($name)) {
            unset($this->cartItemList[$name]);
        }
    }

    /**
     * All prices should be displayed to 2 decimal places
     *
     * @param int $decimal
     * @return string
     */
    public function getTotalPrice($decimal = 2): string
    {
        return number_format((float)$this->totalPrice, $decimal, '.', '');
    }

}


/**
 * the code below is temp code for self test, remove after self test finished
 */
$originalProducts = [
    ["name" => "Sledgehammer", "price" => 125.75],
    ["name" => "Axe", "price" => 190.50],
    ["name" => "Bandsaw", "price" => 562.131],
    ["name" => "Chisel", "price" => 12.9],
    ["name" => "Hacksaw", "price" => 18.45],
];
$productsWithoutHacksaw = [
    ["name" => "Sledgehammer", "price" => 125.75],
    ["name" => "Axe", "price" => 190.50],
    ["name" => "Bandsaw", "price" => 562.131],
    ["name" => "Chisel", "price" => 12.9],
];
/**
 * test: initiate products with array
 */
$productsList = new Products($productsWithoutHacksaw);
$productsListArr = $productsList->getProductList();
//var_dump($productsListArr);
/**
 * test: add one product to products
 */
$productsList->addProduct("Hacksaw", 18.45);
$productsListArr = $productsList->getProductList();
//var_dump($productsListArr);
/**
 * test: initiate shopping cart with $productList
 */
$shoppingCart = new ShoppingCart($productsList);
//var_dump($shoppingCart->getCartItemsList());
/**
 * test: add one cart_item to shopping cart
 */
$shoppingCart->addCartItem("Hacksaw", 18.45);
//var_dump($shoppingCart->getCartItemsList());
/**
 * test: remove one cart_item from shopping cart
 */
$shoppingCart->removeCartItem("Hacksaw");
//var_dump($shoppingCart->getCartItemsList());
/**
 * test: view current products in the cart
 */
$shoppingCartList = $shoppingCart->getCartItemsList();
//var_dump($shoppingCartList);


