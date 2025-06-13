<?php
session_start();
include "view/header.php";
?>

<html>

<head>
    <title>
        rating
    </title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&amp;display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"   />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>


<body class="bg-gray-100 text-gray-900">
    <main class="container mx-auto py-8 px-6">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-2xl font-bold mb-6 text-center">Customer Reviews</h1>

            
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <div class="flex items-center mb-2">
                    <img src="https://down-id.img.susercontent.com/file/id-11134103-7rasd-m4ssjzpetw2g5d.webp" alt="Customer Avatar" class="w-10 h-10 rounded-full mr-3">
                    <div>
                        <p class="font-semibold">Andi Saputra</p>
                        <div class="flex">
                            <i class="fas fa-star text-yellow-500"></i>
                            <i class="fas fa-star text-yellow-500"></i>
                            <i class="fas fa-star text-yellow-500"></i>
                            <i class="fas fa-star text-yellow-500"></i>
                            <i class="fas fa-star text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-700 mb-4">Sepatunya nyaman banget dipakai buat olahraga! ringan dan empuk. Sangat recommended!</p>
                <div class="grid grid-cols-3 gap-4 mt-4">
                    <img src="https://down-id.img.susercontent.com/file/id-11134103-7rbk8-m77ykyw3rfrp83.webp" alt="Review Image 1" class="w-full h-48 object-cover rounded-md">
                    <img src="https://down-id.img.susercontent.com/file/id-11134103-7rbk9-m77ykba8b4jo08.webp" alt="Review Image 2" class="w-full h-48 object-cover rounded-md">
                    <img src="https://down-id.img.susercontent.com/file/id-11134103-7rbk4-m77ykitv9m9836.webp" alt="Review Image 3" class="w-full h-48 object-cover rounded-md">
                </div>

            </div>

           
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <div class="flex items-center mb-2">
                    <img src="https://i.pravatar.cc/40?img=2" alt="Customer Avatar" class="w-10 h-10 rounded-full mr-3">
                    <div>
                        <p class="font-semibold">Dewi Lestari</p>
                        <div class="flex">
                            <i class="fas fa-star text-yellow-500"></i>
                            <i class="fas fa-star text-yellow-500"></i>
                            <i class="fas fa-star text-yellow-500"></i>
                            <i class="fas fa-star text-gray-300"></i>
                            <i class="fas fa-star text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-700 mb-4">Lumayan bagus, cuma ukuran sedikit lebih kecil dari yang saya harapkan. Kualitas bahan oke.</p>
                <div class="grid grid-cols-3 gap-4 mt-4">
                    <img src="https://down-id.img.susercontent.com/file/id-11134103-7rbke-m8b8us22pkwbf2.webp" alt="Review Image 1" class="w-full h-48 object-cover rounded-md">
                    <img src="https://down-id.img.susercontent.com/file/id-11134103-7rbkb-m73g9ulrdbg44b.webp" alt="Review Image 2" class="w-full h-48 object-cover rounded-md">
                    <img src="https://down-id.img.susercontent.com/file/id-11134103-7rasc-m5pdrtji45ja32.webp" alt="Review Image 3" class="w-full h-48 object-cover rounded-md">
                </div>

            </div>

            
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <div class="flex items-center mb-2">
                    <img src="https://i.pravatar.cc/40?img=3" alt="Customer Avatar" class="w-10 h-10 rounded-full mr-3">
                    <div>
                        <p class="font-semibold">Budi Santoso</p>
                        <div class="flex">
                            <i class="fas fa-star text-yellow-500"></i>
                            <i class="fas fa-star text-yellow-500"></i>
                            <i class="fas fa-star text-yellow-500"></i>
                            <i class="fas fa-star text-yellow-500"></i>
                            <i class="fas fa-star text-yellow-500"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-700 mb-4">Produk sangat bagus! Bahan adem dan sesuai dengan deskripsi. Pengiriman juga cepat!</p>
                <div class="grid grid-cols-3 gap-4 mt-4">
                    <img src="https://down-id.img.susercontent.com/file/id-11134103-7rasl-m4vcuvp5lndp79.webp" alt="Review Image 1" class="w-full h-48 object-cover rounded-md">
                    <img src="https://down-id.img.susercontent.com/file/id-11134103-7rase-m38qxvvkr89sea.webp" alt="Review Image 2" class="w-full h-48 object-cover rounded-md">
                    <img src="https://down-id.img.susercontent.com/file/id-11134103-7rash-m5jmk3k2gjswc6.webp" alt="Review Image 3" class="w-full h-48 object-cover rounded-md">
                </div>

            </div>

        </div>
    </main>
</body>


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

</body>

</html>