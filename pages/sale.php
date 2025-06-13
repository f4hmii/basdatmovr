<?php
session_start();
include '../db_connection.php';


if (isset($_GET['add_favorite'])) {
    $productId = intval($_GET['add_favorite']);
    $userId = 1; 
    $quantity = 1;

   
    $stmt = $conn->prepare("SELECT * FROM favorit WHERE pengguna_id = ? AND produk_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
       
        $stmt = $conn->prepare("INSERT INTO favorit (pengguna_id, produk_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userId, $productId, $quantity);
        
        if ($stmt->execute()) {
            echo "Data berhasil ditambahkan ke tabel favorit!";
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Produk sudah ada di favorit.";
    }

   
    header('Location: favorite.php');
    exit;
}

include('../view/header.php');  

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Fashion Sale Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="sale.css" />
</head>

<body>
<div class="carousel">
        <button class="carousel-btn prev-btn fa-solid fa-chevron-left">
            <!-- <i class="fa-solid fa-chevron-left"></i> -->
        </button>
        <div class="carousel-slides">

            <div class="slide">
                <img src="https://www.newbalance.co.id/media/weltpixel/owlcarouselslider/images/s/e/secondary_banner_desktop_2400_x_900-20241220-065408.jpg"
                    alt="">
            </div>
            <div class="slide">
                <img src="https://www.newbalance.co.id/media/weltpixel/owlcarouselslider/images/s/e/secondary_banner-20240805-072521.jpg"
                    alt="">
            </div>
            <div class="slide">
                <img src="https://www.newbalance.co.id/media/weltpixel/owlcarouselslider/images/s/e/secondary_banner_copy-20241118-093422.jpg"
                    alt="">
            </div>
            <div class="slide">
                <img src="https://i.pinimg.com/736x/d5/cf/48/d5cf48081afa823efe25b2275446725b.jpg" alt="">
            </div>

        </div>
        <button class="carousel-btn next-btn fa-solid fa-chevron-right">

        </button>
    </div>

    <header>
        <h1>Fashion Sale Collection</h1>
    </header>
    <br>
    <br>
    <br>
    <br>

   
    <div class="container">
        <!-- Card 1 -->
        <div class="card">
            <span class="discount">-50%</span>
            <img src="https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_IS1657_b%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75"
                alt="jersey" width="150px">
			<a href="sale.php?add_favorite=1" class="heart-link" title="Add to favorite">
				<i class="fa-regular fa-heart" style="cursor:pointer;"></i>
			</a>
			<div class="card-content">
                <p class="title">Tiro 24 T-Shirt Jersey</p>
                <p class="original-price">Rp550.000</p>
                <p class="price">Rp275.000</p>
            </div>
        </div>
        <div class="card">
            <span class="discount">-50%</span>
            <img src="https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_DV4855-570_d%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1080&q=75"
                alt="jersey" width="150px">
			<a href="sale.php?add_favorite=2" class="heart-link" title="Add to favorite">
				<i class="fa-regular fa-heart" style="cursor:pointer;"></i>
			</a>
            <div class="card-content">
                <p class="title">Phoenix Suns Icon</p>
                <p class="original-price">Rp1.379.000</p>
                <p class="price">Rp827.000</p>
            </div>
        </div>

        <div class="card">
            <span class="discount">-50%</span>
            <img src="https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_IL8260_a%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1080&q=75"
                alt="jersey" width="150px">
			<a href="sale.php?add_favorite=3" class="heart-link" title="Add to favorite">
				<i class="fa-regular fa-heart" style="cursor:pointer;"></i>
			</a>
            <div class="card-content">
                <p class="title">Knit Track Suits</p>
                <p class="original-price">Rp1.109.000</p>
                <p class="price">Rp800.000</p>
            </div>
        </div>

        <div class="card">
            <span class="discount">-50%</span>
            <img src=" https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_FB7352-010_c%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75"
                alt="jersey" width="150px">
			<a href="sale.php?add_favorite=4" class="heart-link" title="Add to favorite">
				<i class="fa-regular fa-heart" style="cursor:pointer;"></i>
			</a>
			<div class="card-content">
                <p class="title">Germian Tracktop</p>
                <p class="original-price">Rp1.079.000</p>
                <p class="price">Rp720.000</p>
            </div>
        </div>


        <div class="card">
            <span class="discount">-50%</span>
            <img src="https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_FZ0767-410_b%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75"
                alt="jersey" width="150px">
          <a href="sale.php?add_favorite=5" class="heart-link" title="Add to favorite">
				<i class="fa-regular fa-heart" style="cursor:pointer;"></i>

            <div class="card-content">
                <p class="title">Athletics French Jogger</p>
                <p class="original-price">Rp699.000</p>
                <p class="price">Rp599.000</p>
            </div>
        </div>


        <div class="card">
            <span class="discount">-50%</span>
            <img src="https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_620138_c%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75"
                alt="jersey" width="150px">
			<a href="sale.php?add_favorite=6" class="heart-link" title="Add to favorite">
				<i class="fa-regular fa-heart" style="cursor:pointer;"></i>

            <div class="card-content">
                <p class="title">Jordan Sports Deman</p>
                <p class="original-price">Rp599.000
                <p class="price">Rp280.000</p>
            </div>
        </div>


        <div class="card">
            <span class="discount">-50%</span>
            <img src="https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_FN7690-070_a%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75"
                alt="jersey" width="150px">
			<a href="sale.php?add_favorite=7" class="heart-link" title="Add to favorite">
				<i class="fa-regular fa-heart" style="cursor:pointer;"></i>

            <div class="card-content">
                <p class="title">Cuff Pants</p>
                <p class="original-price">Rp949.000</p>
                <p class="price">Rp430.000</p>
            </div>
        </div>


        <div class="card">
            <span class="discount">-50%</span>
            <img src="https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_MS41520-NNY_b%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75"
                alt="jersey" width="150px">
			<a href="sale.php?add_favorite=8" class="heart-link" title="Add to favorite">
				<i class="fa-regular fa-heart" style="cursor:pointer;"></i>

            <div class="card-content">
                <p class="title">Sports Essentials French</p>
                <p class="original-price">Rp699.000</p>
                <p class="price">Rp320.000</p>
            </div>
        </div>


        <div class="card">
            <span class="discount">-50%</span>
            <img src="https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_IR7468_c%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75"
                alt="jersey" width="150px">
			<a href="sale.php?add_favorite=9" class="heart-link" title="Add to favorite">
				<i class="fa-regular fa-heart" style="cursor:pointer;"></i>

            <div class="card-content">
                <p class="title">Laica Dress</p>
                <p class="original-price">Rp1.200.000</p>
                <p class="price">Rp830.000</p>
            </div>
        </div>


        <div class="card">
            <span class="discount">-50%</span>
            <img src="https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_LA2404001-03_a%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75"
                alt="jersey" width="150px">
			<a href="sale.php?add_favorite=10" class="heart-link" title="Add to favorite">
				<i class="fa-regular fa-heart" style="cursor:pointer;"></i>

            <div class="card-content">
                <p class="title">2in1 Phoenix</p>
                <p class="original-price">Rp900.000</p>
                <p class="price">Rp400.000</p>
            </div>
        </div>


        <div class="card">
            <span class="discount">-50%</span>
            <img src="https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_JE8808_a%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75"
                alt="jersey" width="150px">
           <a href="sale.php?add_favorite=11" class="heart-link" title="Add to favorite">
				<i class="fa-regular fa-heart" style="cursor:pointer;"></i>

            <div class="card-content">
                <p class="title">Red Lady Bra Sport</p>
                <p class="original-price">Rp890.000</p>
                <p class="price">Rp420.000</p>
            </div>
        </div>

        <div class="card">
            <span class="discount">-50%</span>
            <img src="https://jdsports.id/_next/image?url=https%3A%2F%2Fimages.jdsports.id%2Fi%2Fjpl%2Fjd_ANZ0118317_b%3Fw%3D700%26resmode%3Dsharp%26qlt%3D70%26fmt%3Dwebp&w=1920&q=75"
                alt="jersey" width="150px">
		<a href="sale.php?add_favorite=12" class="heart-link" title="Add to favorite">
				<i class="fa-regular fa-heart" style="cursor:pointer;"></i>

            <div class="card-content">
                <p class="title">Pink Soda Sport</p>
                <p class="original-price">Rp300.00</p>
                <p class="price">Rp150.000</p>
            </div>
        </div>
    </div>

        <!-- Add more product cards similarly -->
    </div>
    <footer class="bg-gray-800 text-white mt-12">
   <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-8">
       <div class="py-8">
           <h3 class="text-lg font-semibold mb-4">About Us</h3>
           <p class="text-gray-400">We are a leading sportswear brand committed to providing high-quality
               products
               for athletes and fitness enthusiasts.</p>
           <div class="mt-4">
               <a class="text-gray-400 hover:text-white" href="#"><i class="fab fa-facebook-f"></i></a>
               <a class="ml-4 text-gray-400 hover:text-white" href="#"><i class="fab fa-twitter"></i></a>
               <a class="ml-4 text-gray-400 hover:text-white" href="#"><i class="fab fa-instagram"></i></a>
               <a class="ml-4 text-gray-400 hover:text-white" href="#"><i class="fab fa-linkedin-in"></i></a>
           </div>
       </div>
       <div class="py-8">
           <h3 class="text-lg font-semibold mb-4">Customer Service</h3>
           <ul class="text-gray-400">
               <li class="mb-2"><a class="hover:text-white" href="#">Contact Us</a></li>
               <li class="mb-2"><a class="hover:text-white" href="#">Order Tracking</a></li>
               <li class="mb-2"><a class="hover:text-white" href="#">Returns & Exchanges</a></li>
               <li class="mb-2"><a class="hover:text-white" href="#">Shipping & Delivery</a></li>
               <li class="mb-2"><a class="hover:text-white" href="#">FAQs</a></li>
           </ul>
       </div>
       <div class="py-8">
           <h3 class="text-lg font-semibold mb-4">Newsletter</h3>
           <p class="text-gray-400">Subscribe to get the latest information on new products and upcoming sales.
           </p>
           <form class="mt-4">
               <input class="w-full p-2 rounded-lg text-gray-900" placeholder="Enter your email" type="email" />
               <button class="mt-2 w-full bg-red-600 p-2 rounded-lg hover:bg-red-700"
                   type="submit">Subscribe</button>
           </form>
       </div>
       <div class="mt-8 text-center text-gray-400">
           <p>© 2023 Movr. All rights reserved.</p>
       </div>
</footer>

	
	<style>
	
	* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    outline: none;
    border: none;
    text-decoration: none;
    font-family: "Franklin Gothic Medium", "Arial Narrow", Arial, sans-serif;
  }
  
  body {
  max-width: 100vw;
  max-height: 100vh;
  background-color: #f3f3f3;
  margin: 0; /* Menghapus margin default body */
  
  font-family: 'Roboto', sans-serif;
  }
  
  
  .nav-link {
  position: relative;
  display: inline-block;
  padding: 0.5rem 1rem;
  transition: color 0.3s ease;
  }
  
  .nav-link::after {
  content: '';
  position: absolute;
  width: 100%;
  transform: scaleX(0);
  height: 2px;
  bottom: 0;
  left: 0;
  background-color: #000;
  transform-origin: bottom right;
  transition: transform 0.25s ease-out;
  }
  
  .nav-link:hover::after {
  transform: scaleX(1);
  transform-origin: bottom left;
  }
  
  .nav-icon:hover {
  color: #000;
  transform: scale(1.2);
  }
  
  .hover-image {
  position: relative;
  }
  
  .hover-image img {
  transition: opacity 0.3s ease;
  }
  
  .hover-image img.second {
  position: absolute;
  top: 0;
  left: 0;
  opacity: 0;
  }
  
  .hover-image:hover img.first {
  opacity: 0;
  }
  
  .hover-image:hover img.second {
  opacity: 1;
  }
  
  .btn {
  transition: transform 0.2s ease, background-color 0.2s ease;
  }
  
  .btn:active {
  transform: scale(0.95);
  }
  
  .dropdown-content {
  display: none;
  position: absolute;
  background-color: white;
  min-width: 600px;
  box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
  z-index: 6;
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
  
  }
  
  .dropdown:hover .dropdown-content {
  display: block;
  }
  
  .dropdown-voucher .dropdown-content {
  min-width: 150px;
  
  
  
  }
  
  .dropdown-outlet .dropdown-content {
  min-width: 150px;
  
  }
  
  
  header {
    background-color: #333;
    color: #fff;
    padding: 15px 20px;
    text-align: center;
  }
  
  header h1 {
    margin: 0;
    font-size: 24px;
  }
  
  .container {
    position: relative;
    margin: auto;
    overflow: hidden;
    display: flex;
    flex-wrap: wrap;
    gap: 3rem;
    column-gap: 10px;
    justify-content: space-between;
  }
  
  .discount {
    position: absolute;
    top: 15px;
    left: 1rem;
    padding: .7rem 1rem;
    font-size: 1rem;
    color: white;
    background-color: red;
    /* background: rgba(255, 51, 153, .05); */
    z-index: 1;
    border-radius: .5rem;
  }
  
  .card {
    background-color: #fff;
    border: 1px solid #ddd;
    
    width: 300px;
    overflow: hidden;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
    position: relative;
  }
  
  .card:hover {
    transform: scale(1.05);
  }
  
  .card img {
    width: 100%;
    height: auto;
    position: relative;
    text-align: center;
    overflow: hidden;
    object-fit: cover;
  }
  
  .fa-regular {
    position: absolute;
    top: 320px;
    right: 10px;
    background-color: rgba(255, 255, 255, 0.8);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  }
  
  
  .card-content {
    padding: 15px;
  }
  
  .title {
    font-size: 18px;
    font-weight: bold;
    margin: 10px 0;
    color: #333;
    text-align: left;
  }
  
  .price {
    font-size: 25px;
    color: red;
    margin: 5px 0;
    font-weight: bold;
    text-align: left;
    top: 50%;
  }
  
  .original-price {
    text-decoration: line-through;
    color: #888;
    font-size: 20px;
    text-align: left;
  }
  
  .description {
    font-size: 14px;
    color: #666;
    margin: 10px 0;
  }
  
  .rating {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    font-size: 14px;
    color: #333;
  }
  
  .stars {
    color: #FFD700;
  }
  
  footer {
    background-color: #333;
    color: #fff;
    text-align: center;
    padding: 15px 20px;
    margin-top: 20px;
  }
  
  footer p {
    margin: 0;
    font-size: 14px;
  }
  
  .button-container {
    margin-top: 10px;
  }
  
  .button {
    display: inline-block;
    padding: 10px 20px;
    background-color: #333;
    color: #fff;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-size: 14px;
    transition: background-color 0.3s;
  }
  
  .button:hover {
    background-color: #555;
  }
  
  
  /* styling courosel */
  .carousel {
    position: relative;
    width: 100%;
    overflow: hidden;
    border: 2px solid #ccc;
    border-radius: 10px;
    background-color: white;
    margin-bottom: 20px;
    
  }
  
  
  .carousel-slides {
    display: flex;
    transition: transform 0.5s ease-in-out;
  
  }
  
  .slide {
    min-width: 100%;
    /* max-width: 300px; */
    height: 700px;
    display: flex;
    justify-content: center;
    /* align-items: center; */
    font-size: 2rem;
    font-weight: bold;
    color: #fff;
    user-select: none;
    
  }
  
  .carousel-btn {
    position: absolute;
    width: 32px;
    height: 32px;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    font-size: 2rem;
    padding: 10px;
    cursor: pointer;
    z-index: 1000;
    border-radius: 50%;
  }
  
  .prev-btn {
    left: 10px;
  }
  
  .next-btn {
    right: 10px;
  }
  
  .carousel-btn:focus {
    outline: none;
  }
  
  .carousel-btn:hover {
    background-color: rgba(0, 0, 0, 0.7);
  }
  
  .fa-solid.fa-chevron-left,
  .fa-solid.fa-chevron-right {
    font-size: 8px;
  }
  
	</style>

</body>
</html>
