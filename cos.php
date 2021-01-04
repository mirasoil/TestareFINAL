<?php 
require_once 'controllerFilme.php';
session_start();
$language="engleza";

if(!isset($_SESSION['loggedin'])){
	header('Location:login.html'); //daca utilizatorul nu e logat nu poate accesa cosul
	//ATENTIE-functioneaza doar la inceput, cand sesiunea nu este pornita. Daca ies din cos cu logout si intru tot in cos, in aceeasi fila, cosul va merge
	exit;
}

$user_id = $_SESSION['id'];
//in variabila locala user_id vom prelua id-ul utilizatorului logat din sesiune
//variabila a fost initializata in login.php

$filme = new filme();
if(!empty($_GET['action'])){
	//daca in url avem o variabila action
	switch($_GET['action']){
		case 'add':
			if(!empty($_POST['quantity'])){
				$productResult = $filme->getMovieByCode($_GET['code']);
				//in product-result stocam produsul returnat cu codul preluat din link

				$cartResult = $filme->getCartItemByProduct($productResult[0]['id'], $user_id);
				$_SESSION['quantity'] = $_POST['quantity'];
				//preluam cantitatea din formular si o retinem in sesiune
				header('Location:cos.php'); 
				//reseteaza link-ul ca sa nu se adauge ultima cantitate stocata la fiecare refresh
				
				if(!empty($cartResult)){
					//modificam cantitatea din cos
					$newQuantity = $cartResult[0]['quantity'] + $_SESSION['quantity'];
					//la cantitatea curenta adaugam ce am retinut in sesiune
					$filme->updateCartQuantity($newQuantity, $cartResult[0]['id']);
					

				}else{
					//actualizam cantitatea si in tabela cos
					$filme->addToCart($productResult[0]['id'], $_SESSION['quantity'], $user_id);
				}
			}
			break;

			case 'remove':
				//sterg o inregistrare
				$filme->deleteCartItem($_GET['id']);
				break;

			case 'empty':
				//sterg toate inregistrarile din cos
				$filme->emptyCart($user_id);
				break;
	}
}


 ?>

 <!DOCTYPE html>
 <html>
 <head>
 	<title>Shopping Cart</title>
 	<meta charset="utf-8">
	<link rel="stylesheet" href="styles/main.css" type="text/css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <script src="https://kit.fontawesome.com/a076d05399.js"></script>

	
 </head>
 <body>
 	<?php include 'sections/nav.sec.php'; ?>
  <div id="shopping-cart">
 	<div class="txt-heading">
 		<div class="txt-heading-label" style="font-family: 'Times New Roman', Times, serif; font-weight: bold; font-size: 32px; color: white;">My Cart</div>
    		
 	</div>


 <?php 
 	$cartItem = $filme->getUserCartItem($user_id);

 	if(!empty($cartItem)){
 		$item_total = 0;

  ?>
  <img src="img/locuri.png" style="margin-top: -80px;margin-left: 25%;" />
<form style="text-align: center;margin: 20px;" action="locuriRezervate.php">
  <label for="movie">Choose a seat:</label>
  <select name="movies" id="movies">
  	<optgroup label="Rand1">
    <option value="L1">L1</option>
    <option value="L2">L2</option>
    <option value="L3">L3</option>
    <option value="L4">L4</option>
</optgroup>
<optgroup label="Rand2">
    <option value="L1">L1</option>
    <option value="L2">L2</option>
    <option value="L3">L3</option>
    <option value="L4">L4</option>
</optgroup>
  </select>
  <br><br>
  <input type="submit" name="seatSelecter" value="Select seat">
</form>
<h5 style="text-align: center;">Your selected seat:</h5>

  <table cellpadding="10" cellspacing="1">
	 <tbody>
	 <tr>
		 <th style="text-align: left;"><strong>Name</strong></th>
		 <th style="text-align: left;"><strong>Code</strong></th>
		 <th style="text-align: right;"><strong>Quantity</strong></th>
		 <th style="text-align: right;"><strong>Price</strong></th>
		 <th style="text-align: center;"><strong>Action</strong></th>
	 </tr>	

 <?php 
  foreach ($cartItem as $item) {

 ?>

	<tr>
		 <td style="text-align: left; border-bottom: #F0F0F0 1px solid;"><a href="page.php?page=product&id=<?php echo $item['product_id']; ?>" class="devices"><strong><?php echo $item["name"]; ?></strong></a></td>
		 <td style="text-align: left; border-bottom: #F0F0F0 1px solid;"><?php echo $item["code"]; ?></td>
		 <td style="text-align: right; border-bottom: #F0F0F0 1px solid;"><?php echo $item["quantity"]; ?></td>
		 <td style="text-align: right; border-bottom: #F0F0F0 1px solid;"><?php echo $item["price_eu"]."\xE2\x82\xAc"; ?></td>
		 <td>
		 	<a href="cos.php?action=remove&id=<?php echo $item["cart_id"]; ?>" class="btnRemoveAction"><i class="fas fa-times-circle"></i>Delete product</a>
		 	<span><a href="products.php" class="btnAddAction"><i class="fas fa-cart-plus"></i>Choose another product</a></span>
		 </td>
		 </td>
 	</tr>


<?php 
	$item_total += ($item['price'] * $item['quantity']);
}
 ?>
	<tr>
		 <td colspan="3" align=right><strong>Total:</strong></td>
		 <td align=right><strong><?php echo "$".$item_total; ?></strong></td>
		 <td><a id="btnEmpty" href="cos.php?action=empty"><i class="fas fa-trash"></i>Empty Cart</a></td>
 	</tr>
 	</tbody>
 </table>

<?php 
 }else{
 	//daca nu exista nimic in cos, afisam un mesaj prietenos 
 	echo "<h1 style='text-align: center; padding: 30px;'>Your shopping cart is empty, please check our <a href=\"products.php\" class='devices'>products</a> ! ;) </h1>
 	";
 }
?>

 <div><a href="movies.php" style="text-decoration: none; font-size: 20px;">Go back to shop</a></div>
 <div><a href="logout.php" style="text-decoration: none; font-size: 20px; ">Logout</a></div>
 <div><a href="order.php" style="float: right; font-size: 20px;color: green;" class="btnPlaceOrder">Place order</a></div>



 </body>
 </html>