<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get destination from URL parameter, default to 'hcm'
$destination = $_GET['destination'] ?? 'hcm';

// Travel tips data for all destinations
$travelTips = [
    'taybac' => [
        'title' => 'Tay Bac Travel',
        'about_title' => 'About Northwest Vietnam',
        'about_content' => 'The Northwest region of Vietnam is known for its majestic mountains, ethnic diversity, and scenic terraced rice fields. Provinces like Lao Cai, Yen Bai, Son La, and Dien Bien offer stunning landscapes and cultural richness.',
        'transportation' => [
            'You can travel from Hanoi to the Northwest by car, motorbike, or bus. For destinations like Sapa, overnight trains from Hanoi to Lao Cai are popular. Sleeper buses and limousine vans are also available for comfort.',
            'Within the region, renting motorbikes is ideal for exploring remote villages and mountain passes like O Quy Ho or Khau Pha.'
        ],
        'best_time' => [
            'The best time to visit the Northwest is from September to November (harvest season) and March to May (blooming season). Avoid peak rainy months (July-August) due to landslides in mountainous areas.',
            'September offers golden rice terraces, while spring is filled with blooming peach and plum flowers across the hills.'
        ],
        'places' => [
            '<strong>Day 1:</strong> Explore Sapa – visit Cat Cat Village, Fansipan Peak (via cable car), and the stone church in town. Enjoy the cool climate and local H\'Mong culture.',
            '<strong>Day 2:</strong> Travel to Mu Cang Chai (Yen Bai) via Khau Pha Pass – admire rice terraces at La Pan Tan, Che Cu Nha, and enjoy homestay experiences.',
            '<strong>Day 3:</strong> Head to Moc Chau (Son La) – visit tea hills, Dai Yem Waterfall, and Na Ka Plum Valley. Depending on time, visit Dien Bien Phu historical sites.'
        ],
        'accommodation' => 'Stay in local homestays for an authentic experience or book hotels in towns like Sapa or Mu Cang Chai. Many ethnic-style lodges offer mountain views and traditional meals.',
        'cuisine' => [
            'Grilled stream fish (cá suối nướng)',
            'Buffalo meat in the kitchen (thịt trâu gác bếp)',
            'Sticky rice in bamboo tubes (cơm lam)',
            'Thắng cố – a traditional H\'Mong dish'
        ],
        'shopping' => 'Great souvenirs include brocade textiles, handmade silver jewelry, dried buffalo meat, herbal tea, and local honey. Visit highland markets for authentic products and interaction with locals.',
        'tips' => 'Check the weather before traveling to avoid landslides. Dress warmly in winter months. Be respectful of ethnic customs and ask permission before taking photos. Cash is preferred in remote areas.'
    ],
    
    'phuyen' => [
        'title' => 'Phu Yen Travel',
        'about_title' => 'About Phu Yen',
        'about_content' => 'Phu Yen, located in the South Central Coast of Vietnam, is known for its untouched natural beauty, peaceful beaches, and cinematic landscapes. This province gained popularity through the film "Yellow Flowers on the Green Grass."',
        'transportation' => [
            'You can reach Phu Yen by flight via Tuy Hoa Airport from Hanoi or Ho Chi Minh City. Alternatively, take trains or buses along National Highway 1A, which connects Phu Yen with major cities like Da Nang, Nha Trang, and Quy Nhon.',
            'Within the province, taxis and motorbike rentals are available for getting around and exploring coastal roads and hidden beaches.'
        ],
        'best_time' => [
            'The ideal time to visit Phu Yen is from January to August when the weather is dry and sunny, perfect for sightseeing and beach activities.',
            'Avoid the rainy season from September to December, as sudden showers and rough seas may affect travel plans.'
        ],
        'places' => [
            '<strong>Day 1:</strong> Start with a sunrise at Dai Lanh Cape, then visit Mon Beach and explore the Mui Dien Lighthouse. Relax in the afternoon at Bai Xep Beach – a famous filming location.',
            '<strong>Day 2:</strong> Explore Ganh Da Dia (Da Dia Reef), a unique natural rock formation. Continue to O Loan Lagoon for seafood lunch, and end the day at Mang Lang Church – one of the oldest churches in Vietnam.',
            '<strong>Day 3:</strong> Head to Vung Ro Bay, take a boat ride if available, or go inland to enjoy the peaceful Tuy Hoa city and sample local cuisine before departure.'
        ],
        'accommodation' => 'Most accommodations are in Tuy Hoa city, ranging from budget homestays to beachfront resorts. Some coastal areas also offer eco-lodges and rustic bungalows for nature lovers.',
        'cuisine' => [
            'O Loan Lagoon blood cockles (sò huyết đầm Ô Loan)',
            'Tuna eyeball hotpot (lẩu mắt cá ngừ đại dương)',
            'Chicken rice (cơm gà Phú Yên)',
            'Bánh hỏi lòng heo (fine rice vermicelli with pig organs)'
        ],
        'shopping' => 'Popular souvenirs include Phu Yen fish sauce, dried tuna, rice paper, and handmade coconut products. Visit Tuy Hoa Market or seaside villages to purchase directly from locals.',
        'tips' => 'Wear sun protection and bring plenty of water when exploring outdoor sites. Phu Yen is less commercialized, so prepare cash for local purchases and plan travel routes in advance for remote destinations.'
    ],
    
    'phuquoc' => [
        'title' => 'Phu Quoc Travel',
        'about_title' => 'About Phu Quoc',
        'about_content' => 'Phu Quoc, Vietnam\'s largest island, is located in the Gulf of Thailand. Known for its pristine beaches, turquoise waters, and luxurious resorts, Phu Quoc is a tropical paradise ideal for relaxation, adventure, and seafood cuisine.',
        'transportation' => [
            'You can fly directly to Phu Quoc International Airport from Hanoi, Ho Chi Minh City, and other major cities. Ferries and high-speed boats are also available from Rach Gia or Ha Tien ports in the Mekong Delta.',
            'On the island, motorbikes, taxis, and car rentals are commonly used. Many resorts also offer shuttle services.'
        ],
        'best_time' => [
            'The best time to visit Phu Quoc is during the dry season from November to April, when the sea is calm and the weather is sunny and pleasant.',
            'Rainy season (May to October) can still be enjoyable, especially for travelers seeking fewer crowds and lower prices.'
        ],
        'places' => [
            '<strong>Day 1:</strong> Relax at Long Beach (Bai Truong), visit Dinh Cau Night Market, and explore local temples. Enjoy sunset cocktails on the beach.',
            '<strong>Day 2:</strong> Visit the south: swim at Bai Sao (Star Beach), tour the Phu Quoc Prison Museum, and explore the Coconut Tree Prison. Take the Hon Thom cable car for panoramic views.',
            '<strong>Day 3:</strong> Explore the north: VinWonders amusement park, Vinpearl Safari, and the Grand World complex. Optionally, visit a pepper farm, fish sauce factory, or a pearl farm.'
        ],
        'accommodation' => 'Phu Quoc offers a wide range of accommodations from budget guesthouses to luxury beach resorts, especially along Long Beach, Ong Lang, and Bai Khem.',
        'cuisine' => [
            'Herring salad (gỏi cá trích)',
            'Grilled sea urchin (cầu gai nướng)',
            'Phu Quoc seafood hotpot',
            'Fresh crab, squid, and shrimp from night markets'
        ],
        'shopping' => 'Buy Phu Quoc fish sauce, pepper, pearls, dried seafood, and local handicrafts. The night markets and local stores offer a wide selection of gifts and specialties.',
        'tips' => 'Bring sunscreen and swimwear. Book island tours in advance during peak season. Be cautious when swimming in areas without lifeguards. Respect local customs when visiting temples or fishing villages.'
    ],
    
    'nhatrang' => [
        'title' => 'Nha Trang Travel',
        'about_title' => 'About Nha Trang',
        'about_content' => 'Nha Trang is a coastal city in central Vietnam, known for its beautiful beaches, crystal-clear waters, and vibrant marine life. With a mix of natural beauty and modern tourism, it\'s a popular destination for both local and international travelers.',
        'transportation' => [
            'You can reach Nha Trang by plane via Cam Ranh International Airport, just 35 km away from the city center. Trains and buses from major cities like Ho Chi Minh City or Hanoi also connect conveniently to Nha Trang.',
            'Inside the city, taxis, motorbike rentals, and cyclos are available. Cable cars and boats provide access to nearby islands like Hon Tre and Hon Mun.'
        ],
        'best_time' => [
            'The best time to visit Nha Trang is from January to August, with dry and sunny weather ideal for beach activities and island hopping. September to December sees occasional rains, but tourism remains active.',
            'April and May offer great visibility for diving and snorkeling.'
        ],
        'places' => [
            '<strong>Day 1:</strong> Relax at Nha Trang Beach, visit the Po Nagar Cham Towers, and explore Long Son Pagoda. In the evening, enjoy a seafood dinner along Tran Phu Street.',
            '<strong>Day 2:</strong> Take a boat tour to nearby islands like Hon Mun and Hon Tam. Enjoy snorkeling, coral watching, and swimming in clear waters. Visit the Nha Trang Oceanography Institute.',
            '<strong>Day 3:</strong> Discover VinWonders Nha Trang on Hon Tre Island with cable car access. Enjoy amusement rides, water park, and aquarium. End the day with a mud bath or hot mineral spring spa at Thap Ba or I-Resort.'
        ],
        'accommodation' => 'Nha Trang offers a range of accommodations from beachfront resorts to budget guesthouses. Popular areas include Tran Phu Street and Nguyen Thien Thuat Street.',
        'cuisine' => [
            'Nha Trang seafood (grilled squid, clams, shrimp)',
            'Bun cha ca (fish cake noodle soup)',
            'Nem nướng Ninh Hòa (grilled pork rolls)',
            'Banh can (mini rice pancakes)'
        ],
        'shopping' => 'Visit Dam Market for dried seafood, bird\'s nest products, and local crafts. Other souvenirs include seaweed, handmade seashell jewelry, and Khanh Hoa aloe vera cosmetics.',
        'tips' => 'Bring sunscreen, swimwear, and flip-flops for beach activities. Watch out for strong sun during midday. Bargain when shopping at local markets. Keep an eye on personal belongings at crowded beach areas.'
    ],
    
    'hue' => [
        'title' => 'Hue Travel',
        'about_title' => 'About Hue',
        'about_content' => 'Hue, the former imperial capital of Vietnam, is located in central Vietnam on the banks of the Perfume River. It is known for its rich cultural heritage, ancient architecture, and royal history from the Nguyen Dynasty.',
        'transportation' => [
            'You can reach Hue by plane via Phu Bai International Airport, located about 15 km from the city center. There are also train connections from Hanoi and Ho Chi Minh City, offering scenic views along the way.',
            'Within Hue, you can get around by bicycle, cyclo, motorbike, or taxi. A boat ride along the Perfume River is also a memorable way to explore the city\'s sights.'
        ],
        'best_time' => [
            'The ideal time to visit Hue is from January to April, when the weather is mild and dry. Summer months (May–August) are hot and humid, while the rainy season occurs from September to December.',
            'The biennial Hue Festival, held in even-numbered years, is a great time to experience traditional music, art, and royal customs.'
        ],
        'places' => [
            '<strong>Day 1:</strong> Visit the Imperial City (Citadel), Flag Tower, and the Nine Dynastic Urns. Stop by Thien Mu Pagoda and enjoy a dragon boat ride on the Perfume River in the evening.',
            '<strong>Day 2:</strong> Explore the royal tombs of Minh Mang, Khai Dinh, and Tu Duc. In the afternoon, relax at Thanh Toan Bridge or visit local handicraft villages like Sinh Village (folk painting).',
            '<strong>Day 3:</strong> Depending on your interests, take a day trip to Bach Ma National Park or the Tam Giang Lagoon. Enjoy traditional court music (Nha Nhac) in the evening.'
        ],
        'accommodation' => 'Hue offers various accommodations from riverside resorts to charming homestays near the Citadel. Streets like Le Loi and Nguyen Cong Tru are popular for lodging.',
        'cuisine' => [
            'Bun bo Hue (spicy beef noodle soup)',
            'Com hen (clam rice)',
            'Banh beo, banh loc, banh nam (traditional rice cakes)',
            'Nem lui (grilled pork skewers)'
        ],
        'shopping' => 'Hue is famous for conical hats (non la), handmade incense, royal-inspired artwork, and sesame candy. Dong Ba Market is a popular place to buy souvenirs and local specialties.',
        'tips' => 'Wear modest clothing when visiting temples and tombs. Be prepared for rain if visiting during the wet season. Try guided tours to better understand the rich history of the former imperial city.'
    ],
    
    'hoian' => [
        'title' => 'Hoi An Travel',
        'about_title' => 'About Hoi An',
        'about_content' => 'Hoi An is a UNESCO World Heritage Site located in Quang Nam Province, Central Vietnam. Known for its ancient town, lantern-lit streets, and cultural fusion of East and West, Hoi An is a charming destination filled with history and romance.',
        'transportation' => [
            'The nearest airport is Da Nang International Airport, about 30 km from Hoi An. From the airport, you can take a taxi, shuttle, or private car to reach the town. Trains from Hanoi or Ho Chi Minh City arrive at Da Nang Railway Station.',
            'Within Hoi An, you can explore the Old Town on foot or rent a bicycle to get around easily. For nearby beaches or villages, taxis and motorbikes are available.'
        ],
        'best_time' => [
            'The best time to visit Hoi An is from February to April when the weather is dry and cool. Avoid October and November as they are prone to flooding.',
            'Visit during the Lantern Festival (held on the full moon each month) to experience Hoi An\'s magical atmosphere with lit lanterns and cultural performances.'
        ],
        'places' => [
            '<strong>Day 1:</strong> Explore the Ancient Town – visit the Japanese Covered Bridge, Tan Ky Old House, and Fujian Assembly Hall. Enjoy the lantern-lit streets at night and try traditional food at riverside restaurants.',
            '<strong>Day 2:</strong> Visit Tra Que Vegetable Village and take a cooking class. In the afternoon, relax at An Bang Beach or Cua Dai Beach. End the day with a sunset boat ride on the Thu Bon River.',
            '<strong>Day 3:</strong> Take a half-day tour to My Son Sanctuary (a UNESCO site) or join a bicycle tour through rice fields and coconut forests in Cam Thanh Village.'
        ],
        'accommodation' => 'Hoi An offers a variety of stays, from charming homestays to boutique resorts near the beach or the old town.',
        'cuisine' => [
            'Cao Lau (pork noodle with herbs)',
            'White rose dumplings (bánh bao bánh vạc)',
            'Quang noodles (mì Quảng)',
            'Hoi An chicken rice (cơm gà Hội An)'
        ],
        'shopping' => 'Hoi An is famous for custom-tailored clothes, leather goods, silk lanterns, and handmade ceramics. The Night Market on Nguyen Hoang Street is a great place to shop and experience local culture.',
        'tips' => 'Wear comfortable shoes for walking in the old town. Bargaining is common in markets. Watch out for sudden rain showers—bring an umbrella or raincoat. Respect local customs and dress modestly when visiting temples or historical sites.'
    ],
    
    'hcm' => [
        'title' => 'Ho Chi Minh Travel',
        'about_title' => 'About Ho Chi Minh City',
        'about_content' => 'Located in Southern Vietnam, Ho Chi Minh City is the country\'s largest and most dynamic metropolis. It\'s a key transportation hub, connecting provinces and serving as an international gateway.',
        'transportation' => [
            'The main airport is Tan Son Nhat International Airport, only 20 minutes by taxi from downtown. You can also travel by train from the North via <a href="http://vetau.com.vn" target="_blank">vetau.com.vn</a> or by inter-provincial buses like Mai Linh Express.',
            'Within the city, options include taxis, buses, and motorbike taxis. Bus fares are budget-friendly.'
        ],
        'best_time' => [
            'HCMC has two seasons: dry (Dec-May) and rainy (Jun-Nov). You can visit year-round, but avoid Tet (Lunar New Year) as the city quiets down with people returning to their hometowns.',
            'During Christmas and other festivals, the city is lively with lights and celebrations.'
        ],
        'places' => [
            '<strong>Day 1:</strong> Visit landmarks in District 1 like Ben Thanh Market, Notre-Dame Cathedral, Central Post Office, and the Opera House. In the afternoon, check out Tao Dan Park, Turtle Lake, and Tan Dinh Market. Explore Chinatown in District 5 by evening.',
            '<strong>Day 2:</strong> Take a bus to Cu Chi Tunnels, visit Ben Dinh or Ben Duoc, and 18 Betel Nut Villages. Explore Phu My Hung, Crescent Mall, and enjoy a night cruise on the Saigon River.',
            '<strong>Day 3:</strong> Depending on interest, visit cultural parks (Suoi Tien, Dam Sen), Binh Quoi Village, or Can Gio Mangrove Forest if time permits.'
        ],
        'accommodation' => 'Stay in District 1 for walkability and nightlife. Recommended hotels include Aries Ben Thanh, Phung Hoang Gold Palace, and Ava Saigon 2.',
        'cuisine' => [
            'Banh Mi Hoa Ma (Cao Thang St)',
            'Banh Mi Huynh Hoa (Le Thi Rieng St)',
            'Banh Mi Xiu Mai (Nguyen Thi Minh Khai St)'
        ],
        'shopping' => 'Buy tropical fruits like mango, star apple, and green pomelo. Other great souvenirs: coffee, Soc Trang durian cakes, cashew nuts.',
        'tips' => 'Most restaurants don\'t overcharge. Bargain when shopping in local markets, especially Ben Thanh.'
    ],
    
    'hagiang' => [
        'title' => 'Ha Giang Travel',
        'about_title' => 'About Ha Giang',
        'about_content' => 'Ha Giang is a mountainous province in the northernmost part of Vietnam, known for its rugged landscapes, winding mountain passes, and vibrant ethnic minority cultures. It\'s a dream destination for adventurous travelers seeking off-the-beaten-path experiences.',
        'transportation' => [
            'Ha Giang is about 300 km from Hanoi. You can travel by sleeper bus (8–10 hours) from Hanoi to Ha Giang City. From there, motorbike rental is the most popular and flexible way to explore the region.',
            'For those less comfortable riding, local tours with experienced drivers are available. Road conditions can be challenging, so drive carefully.'
        ],
        'best_time' => [
            'The best time to visit Ha Giang is from September to November (buckwheat flower season) and March to May (pleasant weather and blooming season). Avoid the rainy season (June–August) due to landslides and slippery roads.',
            'October and November are especially scenic, with blooming buckwheat flowers covering the mountain slopes.'
        ],
        'places' => [
            '<strong>Day 1:</strong> Arrive in Ha Giang City and begin the loop to Quan Ba Heaven Gate, Twin Mountains, and stay overnight in Yen Minh or Dong Van.',
            '<strong>Day 2:</strong> Visit Dong Van Old Quarter, Lung Cu Flagpole (the northernmost point of Vietnam), and explore H\'Mong villages. Stay in Dong Van town.',
            '<strong>Day 3:</strong> Travel through Ma Pi Leng Pass – one of the most beautiful mountain passes in Vietnam – and explore Meo Vac. Return to Ha Giang City or extend the trip to Du Gia for waterfalls and homestay experiences.'
        ],
        'accommodation' => 'Ha Giang offers cozy homestays in ethnic villages and small hotels in towns. Dong Van and Meo Vac are popular for overnight stays.',
        'cuisine' => [
            'Thang co (horse meat stew – traditional H\'Mong dish)',
            'Au tau porridge (cháo ấu tẩu)',
            'Men men (steamed cornmeal)',
            'Smoked buffalo meat (thịt trâu gác bếp)'
        ],
        'shopping' => 'Buy handmade brocade textiles, ethnic clothing, buckwheat flower honey, and herbal tea. Weekly markets like Dong Van and Meo Vac Sunday markets are perfect for local products and cultural interaction.',
        'tips' => 'Bring warm clothing, especially in winter. Roads are steep and narrow—drive cautiously. Be respectful of local customs and ask for permission before taking photos. Cell signal can be weak in remote areas—download offline maps in advance.'
    ],
    
    'dalat' => [
        'title' => 'Da Lat Travel',
        'about_title' => 'About Da Lat',
        'about_content' => 'Da Lat, located in the Central Highlands of Vietnam, is known as the "City of Eternal Spring" due to its cool climate, pine forests, and flower gardens. A former French hill station, Da Lat blends European architecture with romantic scenery.',
        'transportation' => [
            'You can reach Da Lat by plane via Lien Khuong Airport, located about 30 km from the city center. Buses from Ho Chi Minh City, Nha Trang, or nearby cities are also available. The roads to Da Lat offer scenic views of mountain passes and valleys.',
            'Within the city, taxis, motorbike rentals, and electric carts are common ways to get around. Many attractions are within a short drive or walk from the city center.'
        ],
        'best_time' => [
            'The ideal time to visit Da Lat is from November to March, during the dry season when the weather is cool and flowers bloom. The Flower Festival, held every two years in December, is a major event worth attending.',
            'Spring (late January to March) features cherry blossoms and beautiful garden displays.'
        ],
        'places' => [
            '<strong>Day 1:</strong> Visit Xuan Huong Lake, Da Lat Flower Park, and Lam Vien Square. Explore the French Quarter with colonial villas and have coffee at a hillside cafe.',
            '<strong>Day 2:</strong> Discover Datanla Waterfall (via alpine coaster), Crazy House, and Truc Lam Zen Monastery by cable car. In the evening, stroll the Da Lat Night Market.',
            '<strong>Day 3:</strong> Visit Langbiang Mountain for panoramic views and cultural encounters with local ethnic groups. Optional stops include Clay Tunnel, Valley of Love, or Domaine de Marie Church.'
        ],
        'accommodation' => 'Choose from charming homestays, French-inspired hotels, or romantic resorts with views of pine forests and valleys.',
        'cuisine' => [
            'Bánh tráng nướng (Vietnamese pizza)',
            'Lẩu gà lá é (chicken hotpot with basil)',
            'Soy milk at night markets',
            'Fresh strawberries and artichoke tea'
        ],
        'shopping' => 'Popular items include dried fruits, Da Lat wine, jam, fresh flowers, and local handicrafts. The Night Market is ideal for souvenirs and street snacks.',
        'tips' => 'Pack warm clothes, especially for evenings and early mornings. Bring cash for local markets. Watch out for foggy roads if you\'re riding a motorbike. Respect quiet zones at religious and cultural sites.'
    ]
];

// Check if destination exists, default to hcm
if (!isset($travelTips[$destination])) {
    $destination = 'hcm';
}

$tip = $travelTips[$destination];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tip['title']) ?></title>
    <link rel="stylesheet" href="../css/traveltips.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
</head>
<body>

<?php include __DIR__ . '/../header.php'; ?>

<body>
    <main class="container">
        <section class="section">
            <h2><?= htmlspecialchars($tip['about_title']) ?></h2>
            <p><?= $tip['about_content'] ?></p>
        </section>

        <section class="section">
            <h2>Transportation</h2>
            <?php foreach ($tip['transportation'] as $transport): ?>
                <p><?= $transport ?></p>
            <?php endforeach; ?>
        </section>

        <section class="section">
            <h2>Best Time to Visit</h2>
            <?php foreach ($tip['best_time'] as $time): ?>
                <p><?= $time ?></p>
            <?php endforeach; ?>
        </section>

        <section class="section">
            <h2>Places to Visit</h2>
            <?php foreach ($tip['places'] as $place): ?>
                <p><?= $place ?></p>
            <?php endforeach; ?>
        </section>

        <section class="section">
            <h2>Accommodation & Cuisine</h2>
            <p><?= $tip['accommodation'] ?></p>
            <?php if ($destination === 'hcm'): ?>
                <p>Enjoy Vietnamese and international cuisines. Don't miss local specialties like:</p>
            <?php else: ?>
                <p><?php if ($destination === 'taybac'): ?>Local specialties to try:<?php else: ?>Must-try <?php if ($destination === 'dalat'): ?>dishes in Da Lat:<?php else: ?>local dishes:<?php endif; ?><?php endif; ?></p>
            <?php endif; ?>
            <ul>
                <?php foreach ($tip['cuisine'] as $dish): ?>
                    <li><?= $dish ?></li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="section">
            <h2>Shopping & Souvenirs</h2>
            <p><?= $tip['shopping'] ?></p>
        </section>

        <section class="section">
            <h2>Tips & Cautions</h2>
            <p><?= $tip['tips'] ?></p>
        </section>
    </main>
    
<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>