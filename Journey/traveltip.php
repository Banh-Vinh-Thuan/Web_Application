<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get destination from URL parameter, default to 'hcm'
$destination = $_GET['destination'] ?? 'hcm';

// Travel tips data for all destinations with seasonal information
$travelTips = [
    'taybac' => [
        'title' => 'Tay Bac Travel',
        'about_title' => 'About Northwest Vietnam',
        'about_content' => 'The Northwest region of Vietnam is renowned for its breathtaking natural landscapes and rich cultural heritage. Characterized by towering mountains, verdant valleys, and iconic terraced rice fields, this area is home to a diverse array of ethnic minority communities, each with distinct traditions, costumes, and lifestyles. Key provinces such as Lao Cai, Yen Bai, Son La, and Dien Bien offer visitors an immersive experience into authentic Vietnamese highland culture, complemented by dramatic scenery, traditional festivals, and warm local hospitality.',
        'coordinates' => ['latitude' => 22.3380, 'longitude' => 103.8442],
        'season_info' => [
            'good_months' => [3, 4, 5, 9, 10, 11],
            'ok_months' => [1, 2, 12],
            'bad_months' => [6, 7, 8],
            'notes' => 'Best for trekking and photography during spring (Mar-May) and autumn (Sep-Nov). Golden rice terraces in September-October. Avoid summer due to heavy rains and potential landslides.'
        ],
        'transportation' => [
            'You can travel from Hanoi to the Northwest by car, motorbike, or bus. For destinations like Sapa, overnight trains from Hanoi to Lao Cai are popular. Sleeper buses and limousine vans are also available for comfort.',
            'Within the region, renting motorbikes is ideal for exploring remote villages and mountain passes like O Quy Ho or Khau Pha.'
        ],
        'best_time' => [
            'The best time to visit the Northwest is from September to November (harvest season) and March to May (blooming season). Avoid peak rainy months (July-August) due to landslides in mountainous areas.',
            'September offers golden rice terraces, while spring is filled with blooming peach and plum flowers across the hills.'
        ],
        'places' => [
            [
                'day' => 'Day 1',
                'description' => "Begin your journey in Sapa by exploring Cat Cat Village — a charming village famous for its scenic beauty and traditional H'Mong culture, attracting countless visitors each year. Take the cable car ride up to Fansipan Peak, known as the 'Roof of Indochina', a sacred and majestic site offering panoramic views. End your day with a visit to the iconic stone church in the town center. Enjoy Sapa's cool mountain climate and vibrant ethnic culture.",
                'images' => ['../images/taybac_D1.jpg', '../images/taybac_D1_1.jpg', '../images/taybac_D1_2.jpg', '../images/taybac_D1_3.jpg']
            ],
            [
                'day' => 'Day 2',
                'description' => "Travel to Mu Cang Chai (Yen Bai) via the breathtaking Khau Pha Pass — one of Vietnam’s most scenic mountain passes. Marvel at the stunning rice terraces in La Pan Tan and Che Cu Nha, recognized as national heritage landscapes. Experience authentic local life through a warm and memorable homestay with friendly ethnic minority communities.",
                'images' => ['../images/taybac_D2.jpg', '../images/taybac_D2_1.jpg', '../images/taybac_D2_2.jpg']
            ],
            [
                'day' => 'Day 3',
                'description' => "Continue to Moc Chau (Son La) — explore the lush green tea hills stretching across rolling landscapes, visit the picturesque Dai Yem Waterfall, and stroll through the scenic Na Ka Plum Valley, especially stunning during blooming season. If time permits, journey further to discover the heroic Dien Bien Phu historical sites, rich in national pride.",
                'images' => ['../images/taybac_D3.jpg', '../images/taybac_D3_1.jpg', '../images/taybac_D3_2.jpg', '../images/taybac_D3_3.jpg']
            ]
        ],
        'accommodation' => 'Stay in local homestays for an authentic experience or book hotels in towns like Sapa or Mu Cang Chai. Many ethnic-style lodges offer mountain views and traditional meals.',
        'cuisine' => [
            [
                'name' => 'Grilled stream fish (cá suối nướng)',
                'image' => '../images/taybac_C1.jpg'
            ],
            [
                'name' => 'Buffalo meat in the kitchen (thịt trâu gác bếp)',
                'image' => '../images/taybac_C2.jpg'
            ],
            [
                'name' => 'Sticky rice in bamboo tubes (cơm lam)',
                'image' => '../images/taybac_C3.jpg'
            ],
            [
                'name' => 'Thắng cố – a traditional H\'Mong dish',
                'image' => '../images/taybac_C4.jpg'
            ]
        ],
        'shopping' => 'Great souvenirs include brocade textiles, handmade silver jewelry, dried buffalo meat, herbal tea, and local honey. Visit highland markets for authentic products and interaction with locals.',
        'tips' => 'Check the weather before traveling to avoid landslides. Dress warmly in winter months. Be respectful of ethnic customs and ask permission before taking photos. Cash is preferred in remote areas.'
    ],
    
    'phuyen' => [
        'title' => 'Phu Yen Travel',
        'about_title' => 'About Phu Yen',
        'about_content' => 'Phu Yen, situated along Vietnam’s South Central Coast, is celebrated for its pristine natural beauty, serene coastline, and picturesque landscapes that remain largely unspoiled by mass tourism. The province captivates visitors with its tranquil beaches, lush green hills, and dramatic coastal cliffs. Phu Yen rose to national prominence through its appearance in the acclaimed Vietnamese film "Yellow Flowers on the Green Grass," which showcased its poetic charm and rural simplicity. Today, it continues to attract travelers seeking authenticity, natural serenity, and cultural richness.',
        'coordinates' => ['latitude' => 13.0882, 'longitude' => 109.0929],
        'season_info' => [
            'good_months' => [1, 2, 3, 4, 5, 6, 7, 8],
            'ok_months' => [12],
            'bad_months' => [9, 10, 11],
            'notes' => 'Perfect dry season from January to August for beaches and outdoor activities. Avoid September-November due to heavy rains and storms. Best photography conditions during early morning and late afternoon.'
        ],
        'transportation' => [
            'You can reach Phu Yen by flight via Tuy Hoa Airport from Hanoi or Ho Chi Minh City. Alternatively, take trains or buses along National Highway 1A, which connects Phu Yen with major cities like Da Nang, Nha Trang, and Quy Nhon.',
            'Within the province, taxis and motorbike rentals are available for getting around and exploring coastal roads and hidden beaches.'
        ],
        'best_time' => [
            'The ideal time to visit Phu Yen is from January to August when the weather is dry and sunny, perfect for sightseeing and beach activities.',
            'Avoid the rainy season from September to December, as sudden showers and rough seas may affect travel plans.'
        ],
        'places' => [
            [
                'day' => 'Day 1',
                'description' => "Begin your journey with a breathtaking sunrise at Dai Lanh Cape — one of the easternmost points of Vietnam. Continue to the serene Mon Beach and climb up to explore the historic Mui Dien Lighthouse, offering panoramic ocean views. In the afternoon, unwind at Bai Xep Beach, a peaceful coastal gem featured in popular Vietnamese films.",
                'images' => ['../images/phuyen_D1.jpg', '../images/phuyen_D1_1.jpg', '../images/phuyen_D1_2.jpg', '../images/phuyen_D1_3.jpg']
            ],
            [
                'day' => 'Day 2',
                'description' => "Discover the geological wonder of Ganh Da Dia (Da Dia Reef), where thousands of basalt columns create a stunning natural mosaic by the sea. Enjoy a fresh seafood lunch at the tranquil O Loan Lagoon, known for its serene waters and local delicacies. Conclude the day with a visit to Mang Lang Church — one of Vietnam’s oldest and most historically significant churches, blending Gothic architecture with coastal charm.",
                'images' => ['../images/phuyen_D2.jpg', '../images/phuyen_D2_1.jpg', '../images/phuyen_D2_2.jpg']
            ],
            [
                'day' => 'Day 3',
                'description' => "Begin the day with a visit to Vung Ro Bay — a tranquil coastal area steeped in wartime history and natural beauty. If conditions allow, enjoy a scenic boat ride across the calm bay waters. Alternatively, head inland to explore the laid-back charm of Tuy Hoa City, where you can savor authentic local cuisine before concluding your journey.",
                'images' => ['../images/phuyen_D3.jpg', '../images/phuyen_D3_1.jpg', '../images/phuyen_D3_2.jpg']
            ]
        ],
        'accommodation' => 'Most accommodations are in Tuy Hoa city, ranging from budget homestays to beachfront resorts. Some coastal areas also offer eco-lodges and rustic bungalows for nature lovers.',
        'cuisine' => [
            [
                'name' => 'O Loan Lagoon Blood Cockles (Sò Huyết Đầm Ô Loan)',
                'image' => '../images/phuyen_C1.jpg'
            ],
            [
                'name' => 'Tuna Eyeball Hotpot (Lẩu Mắt Cá Ngừ Đại Dương)',
                'image' => '../images/phuyen_C2.jpg'
            ],
            [
                'name' => 'Chicken Rice (Cơm Gà Phú Yên)',
                'image' => '../images/phuyen_C3.jpg'
            ],
            [
                'name' => 'Bánh Hỏi Lòng Heo (Fine Rice Vermicelli With Pig Organs)',
                'image' => '../images/phuyen_C4.jpg'
            ]
        ],
        'shopping' => 'Popular souvenirs include Phu Yen fish sauce, dried tuna, rice paper, and handmade coconut products. Visit Tuy Hoa Market or seaside villages to purchase directly from locals.',
        'tips' => 'Wear sun protection and bring plenty of water when exploring outdoor sites. Phu Yen is less commercialized, so prepare cash for local purchases and plan travel routes in advance for remote destinations.'
    ],
    
    'phuquoc' => [
        'title' => 'Phu Quoc Travel',
        'about_title' => 'About Phu Quoc',
        'about_content' => 'Phu Quoc, the largest island in Vietnam, is nestled in the Gulf of Thailand and offers a captivating blend of natural beauty and modern luxury. Renowned for its crystal-clear turquoise waters, powdery white-sand beaches, and lush tropical forests, the island has emerged as a premier destination for both leisure and adventure. Visitors can explore vibrant coral reefs, enjoy world-class resorts and spas, and savor the island’s famed seafood cuisine. With its year-round sunshine and relaxed atmosphere, Phu Quoc stands as a true tropical paradise for travelers seeking both tranquility and excitement.',
        'coordinates' => ['latitude' => 10.2899, 'longitude' => 103.9840],
        'season_info' => [
            'good_months' => [11, 12, 1, 2, 3, 4],
            'ok_months' => [10, 5],
            'bad_months' => [6, 7, 8, 9],
            'notes' => 'Dry season (Nov-Apr) offers perfect beach weather with calm seas and sunny skies. Wet season (May-Oct) brings afternoon showers but fewer crowds and lower prices. Best for diving and snorkeling during dry months.'
        ],
        'transportation' => [
            'You can fly directly to Phu Quoc International Airport from Hanoi, Ho Chi Minh City, and other major cities. Ferries and high-speed boats are also available from Rach Gia or Ha Tien ports in the Mekong Delta.',
            'On the island, motorbikes, taxis, and car rentals are commonly used. Many resorts also offer shuttle services.'
        ],
        'best_time' => [
            'The best time to visit Phu Quoc is during the dry season from November to April, when the sea is calm and the weather is sunny and pleasant.',
            'Rainy season (May to October) can still be enjoyable, especially for travelers seeking fewer crowds and lower prices.'
        ],
        'places' => [
            [
                'day' => 'Day 1',
                'description' => "Ease into island life with a relaxing stroll along Long Beach (Bai Truong), famous for its golden sands and calm waters. In the evening, wander through the vibrant Dinh Cau Night Market to sample street food and shop for souvenirs. Visit local temples to immerse yourself in the island’s spiritual culture, and end the day with sunset cocktails by the beach.",
                'images' => ['../images/phuquoc_D1.jpg', '../images/phuquoc_D1_1.jpg', '../images/phuquoc_D1_2.jpg', '../images/phuquoc_D1_3.jpg']
            ],
            [
                'day' => 'Day 2',
                'description' => "Head south to discover Phu Quoc’s highlights. Start your day with a refreshing swim at Bai Sao (Star Beach), known for its powdery white sand and turquoise waters. Then, delve into the island’s wartime history with visits to the Phu Quoc Prison Museum and the Coconut Tree Prison. Cap off your adventure with a ride on the Hon Thom cable car — the longest over-sea cable car in the world — offering breathtaking panoramic views over the islands.",
                'images' => ['../images/phuquoc_D2.jpg', '../images/phuquoc_D2_1.jpg', '../images/phuquoc_D2_2.jpg', '../images/phuquoc_D2_3.jpg']
            ],
            [
                'day' => 'Day 3',
                'description' => "Venture to the north of Phu Quoc for a day of excitement and discovery. Unleash your inner child at VinWonders – a world-class amusement park with thrilling rides and themed zones. Get close to wildlife at Vinpearl Safari, Vietnam’s largest semi-wildlife conservation park. Stroll through the dazzling Grand World complex, known as the ‘sleepless city’ for its vibrant entertainment and cultural shows. For a local touch, you may also visit a traditional pepper farm, a fish sauce factory, or a pearl farm to learn about Phu Quoc’s famous specialties.",
                'images' => ['../images/phuquoc_D3.jpg', '../images/phuquoc_D3_1.jpg', '../images/phuquoc_D3_2.jpg', '../images/phuquoc_D3_3.jpg']
            ]
        ],
        'accommodation' => 'Phu Quoc offers a wide range of accommodations from budget guesthouses to luxury beach resorts, especially along Long Beach, Ong Lang, and Bai Khem.',
        'cuisine' => [
            [
                'name' => 'Herring Salad (Gỏi Cá Trích)',
                'image' => '../images/phuquoc_C1.jpg'
            ],
            [
                'name' => 'Grilled Sea Urchin (Cầu Gai Nướng)',
                'image' => '../images/phuquoc_C2.jpg'
            ],
            [
                'name' => 'Phu Quoc Seafood Hotpot',
                'image' => '../images/phuquoc_C3.jpg'
            ],
            [
                'name' => 'Fresh Crab, Squid, And Shrimp From Night Markets',
                'image' => '../images/phuquoc_C4.jpg'
            ]
        ],
        'shopping' => 'Buy Phu Quoc fish sauce, pepper, pearls, dried seafood, and local handicrafts. The night markets and local stores offer a wide selection of gifts and specialties.',
        'tips' => 'Bring sunscreen and swimwear. Book island tours in advance during peak season. Be cautious when swimming in areas without lifeguards. Respect local customs when visiting temples or fishing villages.'
    ],
    
    'nhatrang' => [
        'title' => 'Nha Trang Travel',
        'about_title' => 'About Nha Trang',
        'about_content' => 'Nha Trang, a coastal gem in central Vietnam, is renowned for its stunning beaches, crystal-clear waters, and thriving marine biodiversity. This dynamic city seamlessly blends natural charm with modern tourism, offering a wide array of activities ranging from water sports and island hopping to cultural exploration and wellness retreats. Its long stretch of coastline, framed by lush mountains and dotted with offshore islets, creates an idyllic setting for relaxation and adventure alike. Popular among both domestic and international visitors, Nha Trang continues to thrive as one of Vietnam’s premier seaside destinations.',
        'coordinates' => ['latitude' => 12.2388, 'longitude' => 109.1967],
        'season_info' => [
            'good_months' => [1, 2, 3, 4, 5, 6, 7, 8],
            'ok_months' => [12],
            'bad_months' => [9, 10, 11],
            'notes' => 'Excellent weather Jan-Aug with minimal rainfall, perfect for beach activities and island hopping. April-May ideal for diving with best visibility. Avoid Oct-Nov due to heavy rains and potential typhoons.'
        ],
        'transportation' => [
            'You can reach Nha Trang by plane via Cam Ranh International Airport, just 35 km away from the city center. Trains and buses from major cities like Ho Chi Minh City or Hanoi also connect conveniently to Nha Trang.',
            'Inside the city, taxis, motorbike rentals, and cyclos are available. Cable cars and boats provide access to nearby islands like Hon Tre and Hon Mun.'
        ],
        'best_time' => [
            'The best time to visit Nha Trang is from January to August, with dry and sunny weather ideal for beach activities and island hopping. September to December sees occasional rains, but tourism remains active.',
            'April and May offer great visibility for diving and snorkeling.'
        ],
        'places' => [
            [
                'day' => 'Day 1',
                'description' => "Kick off your Nha Trang adventure with a relaxing day on the city’s sun-kissed beach, perfect for swimming or simply soaking in the sea breeze. Visit the Po Nagar Cham Towers — a centuries-old Hindu temple complex rich in history and architectural beauty. Continue to Long Son Pagoda, home to a giant white Buddha statue overlooking the city. As the sun sets, savor a delicious seafood dinner along the lively Tran Phu Street, known for its ocean views and vibrant atmosphere.",
                'images' => ['../images/nhatrang_D1.jpg', '../images/nhatrang_D1_1.jpg', '../images/nhatrang_D1_2.jpg', '../images/nhatrang_D1_3.jpg']
            ],
            [
                'day' => 'Day 2',
                'description' => "Embark on an island-hopping boat tour to explore the stunning beauty of Nha Trang Bay. Visit Hon Mun and Hon Tam — famous for their crystal-clear waters and vibrant marine life. Enjoy snorkeling, coral reef viewing, and swimming in the tropical sea. In the afternoon, head back to the mainland to explore the Nha Trang Oceanography Institute, where you can learn about Vietnam’s rich underwater world through fascinating exhibits and marine specimens.",
                'images' => ['../images/nhatrang_D2.jpg', '../images/nhatrang_D2_1.jpg', '../images/nhatrang_D2_2.jpg', '../images/nhatrang_D2_3.jpg']
            ],
            [
                'day' => 'Day 3',
                'description' => "Spend an exciting day at VinWonders Nha Trang on Hon Tre Island, accessible by a scenic over-sea cable car ride. Experience a world of fun with thrilling amusement rides, a tropical water park, and a vibrant aquarium showcasing marine life. After returning to the mainland, unwind with a rejuvenating mud bath or soak in hot mineral springs at Thap Ba or I-Resort — the perfect way to relax and recharge before departure.",
                'images' => ['../images/nhatrang_D3.jpg', '../images/nhatrang_D3_1.jpg', '../images/nhatrang_D3_2.jpg', '../images/nhatrang_D3_3.jpg']
            ]
        ],
        'accommodation' => 'Nha Trang offers a range of accommodations from beachfront resorts to budget guesthouses. Popular areas include Tran Phu Street and Nguyen Thien Thuat Street.',
        'cuisine' => [
            [
                'name' => 'Nha Trang seafood (grilled squid, clams, shrimp)',
                'image' => '../images/nhatrang_C1.jpg'
            ],
            [
                'name' => 'Bun cha ca (fish cake noodle soup)',
                'image' => '../images/nhatrang_C2.jpg'
            ],
            [
                'name' => 'Nem nướng Ninh Hòa (grilled pork rolls)',
                'image' => '../images/nhatrang_C3.jpg'
            ],
            [
                'name' => 'Banh can (mini rice pancakes)',
                'image' => '../images/nhatrang_C4.jpg'
            ]
        ],
        'shopping' => 'Visit Dam Market for dried seafood, bird\'s nest products, and local crafts. Other souvenirs include seaweed, handmade seashell jewelry, and Khanh Hoa aloe vera cosmetics.',
        'tips' => 'Bring sunscreen, swimwear, and flip-flops for beach activities. Watch out for strong sun during midday. Bargain when shopping at local markets. Keep an eye on personal belongings at crowded beach areas.'
    ],
    
    'hue' => [
        'title' => 'Hue Travel',
        'about_title' => 'About Hue',
        'about_content' => 'Hue, the former imperial capital of Vietnam, is situated in central Vietnam along the tranquil banks of the Perfume River. Steeped in royal legacy, the city is renowned for its well-preserved historical architecture, including the majestic Imperial City, ancient pagodas, royal tombs, and traditional garden houses. As the heart of the Nguyen Dynasty, Hue served as the political, cultural, and religious center of the country during the 19th and early 20th centuries. Today, it stands as a UNESCO World Heritage Site, offering visitors a profound journey through Vietnam’s regal past, enriched by elegant court music, refined cuisine, and solemn historical landmarks.',
        'coordinates' => ['latitude' => 16.4637, 'longitude' => 107.5909],
        'season_info' => [
            'good_months' => [1, 2, 3, 4],
            'ok_months' => [5, 6, 12],
            'bad_months' => [7, 8, 9, 10, 11],
            'notes' => 'Best weather Jan-Apr with mild temperatures and minimal rain. Rainy season Sep-Dec can be challenging for sightseeing. Summer months (May-Aug) are hot and humid but manageable. Hue Festival occurs in even years.'
        ],
        'transportation' => [
            'You can reach Hue by plane via Phu Bai International Airport, located about 15 km from the city center. There are also train connections from Hanoi and Ho Chi Minh City, offering scenic views along the way.',
            'Within Hue, you can get around by bicycle, cyclo, motorbike, or taxi. A boat ride along the Perfume River is also a memorable way to explore the city\'s sights.'
        ],
        'best_time' => [
            'The ideal time to visit Hue is from January to April, when the weather is mild and dry. Summer months (May–August) are hot and humid, while the rainy season occurs from September to December.',
            'The biennial Hue Festival, held in even-numbered years, is a great time to experience traditional music, art, and royal customs.'
        ],
        'places' => [
            [
                'day' => 'Day 1',
                'description' => "Step into the royal past with a visit to the Imperial City (Citadel), where ancient walls, palaces, and gates reveal the grandeur of Vietnam’s Nguyen Dynasty. Admire the historic Flag Tower and the majestic Nine Dynastic Urns, symbols of imperial power. Continue to the serene Thien Mu Pagoda, perched above the Perfume River. As the sun sets, enjoy a peaceful dragon boat ride along the river, immersing yourself in the poetic charm of Hue.",
                'images' => ['../images/hue_D1.jpg', '../images/hue_D1_1.jpg', '../images/hue_D1_2.jpg', '../images/hue_D1_3.jpg']
            ],
            [
                'day' => 'Day 2',
                'description' => "Delve deeper into Hue’s imperial heritage with visits to the royal tombs of Emperors Minh Mang, Khai Dinh, and Tu Duc — each offering unique architecture, serene landscapes, and stories of the Nguyen Dynasty’s legacy. In the afternoon, unwind at the peaceful Thanh Toan Bridge, an ancient tile-roofed structure nestled amid rice fields. Alternatively, immerse yourself in local culture by exploring traditional handicraft villages like Sinh Village, famed for its centuries-old folk painting art.",
                'images' => ['../images/hue_D2.jpg', '../images/hue_D2_1.jpg', '../images/hue_D2_2.jpg', '../images/hue_D2_3.jpg']
            ],
            [
                'day' => 'Day 3',
                'description' => "Choose your own adventure on the final day in Hue. Nature lovers can journey to Bach Ma National Park, a lush mountain escape with forest trails, waterfalls, and cool climate. Alternatively, unwind with a tranquil visit to Tam Giang Lagoon, where fishing villages and sunset views offer a glimpse into peaceful rural life. In the evening, immerse yourself in the elegance of Nha Nhac — traditional royal court music recognized by UNESCO as an Intangible Cultural Heritage of Humanity.",
                'images' => ['../images/hue_D3.jpg', '../images/hue_D3_1.jpg', '../images/hue_D3_2.jpg', '../images/hue_D3_3.jpg']
            ]
        ],
        'accommodation' => 'Hue offers various accommodations from riverside resorts to charming homestays near the Citadel. Streets like Le Loi and Nguyen Cong Tru are popular for lodging.',
        'cuisine' => [
            [
                'name' => 'Bun bo Hue (spicy beef noodle soup)',
                'image' => '../images/hue_C1.jpg'
            ],
            [
                'name' => 'Com hen (clam rice)',
                'image' => '../images/hue_C2.jpg'
            ],
            [
                'name' => 'Banh beo, banh loc, banh nam (traditional rice cakes)',
                'image' => '../images/hue_C3.jpg'
            ],
            [
                'name' => 'Nem lui (grilled pork skewers)',
                'image' => '../images/hue_C4.jpg'
            ]
        ],
        'shopping' => 'Hue is famous for conical hats (non la), handmade incense, royal-inspired artwork, and sesame candy. Dong Ba Market is a popular place to buy souvenirs and local specialties.',
        'tips' => 'Wear modest clothing when visiting temples and tombs. Be prepared for rain if visiting during the wet season. Try guided tours to better understand the rich history of the former imperial city.'
    ],
    
    'hoian' => [
        'title' => 'Hoi An Travel',
        'about_title' => 'About Hoi An',
        'about_content' => 'Hoi An, a UNESCO World Heritage Site nestled in Quang Nam Province in Central Vietnam, is celebrated for its exceptionally well-preserved Ancient Town and timeless charm. Once a thriving Southeast Asian trading port from the 15th to 19th centuries, the town reflects a unique blend of Vietnamese, Chinese, Japanese, and European influences in its architecture, culture, and cuisine. Visitors are enchanted by its lantern-lit streets, wooden shop-houses, centuries-old temples, and riverside cafes. Beyond its historical allure, Hoi An offers a romantic and tranquil atmosphere, making it a beloved destination for cultural exploration, culinary experiences, and leisurely strolls through living history.',
        'coordinates' => ['latitude' => 15.8801, 'longitude' => 108.3380],
        'season_info' => [
            'good_months' => [2, 3, 4, 5, 6, 7, 8],
            'ok_months' => [1, 9, 12],
            'bad_months' => [10, 11],
            'notes' => 'Perfect weather Feb-Aug with warm temperatures and minimal rainfall. Lantern Festival monthly during full moon is magical. Oct-Nov prone to flooding - avoid this period. Dry season ideal for ancient town exploration.'
        ],
        'transportation' => [
            'The nearest airport is Da Nang International Airport, about 30 km from Hoi An. From the airport, you can take a taxi, shuttle, or private car to reach the town. Trains from Hanoi or Ho Chi Minh City arrive at Da Nang Railway Station.',
            'Within Hoi An, you can explore the Old Town on foot or rent a bicycle to get around easily. For nearby beaches or villages, taxis and motorbikes are available.'
        ],
        'best_time' => [
            'The best time to visit Hoi An is from February to April when the weather is dry and cool. Avoid October and November as they are prone to flooding.',
            'Visit during the Lantern Festival (held on the full moon each month) to experience Hoi An\'s magical atmosphere with lit lanterns and cultural performances.'
        ],
        'places' => [
            [
                'day' => 'Day 1',
                'description' => "Wander through the charming streets of Hoi An Ancient Town, a UNESCO World Heritage Site steeped in history and romance. Visit iconic landmarks like the Japanese Covered Bridge, the beautifully preserved Tan Ky Old House, and the ornate Fujian Assembly Hall. As night falls, marvel at the glowing lantern-lit alleys and savor traditional dishes at riverside restaurants overlooking the Hoai River.",
                'images' => ['../images/hoian_D1.jpg', '../images/hoian_D1_1.jpg', '../images/hoian_D1_2.jpg', '../images/hoian_D1_3.jpg']
            ],
            [
                'day' => 'Day 2',
                'description' => "Begin your day with a visit to Tra Que Vegetable Village, where you can learn about organic farming and join a hands-on cooking class to prepare local specialties. In the afternoon, unwind on the golden sands of An Bang or Cua Dai Beach. As the sun sets, take a peaceful boat ride along the Thu Bon River, soaking in the tranquil scenery of the countryside.",
                'images' => ['../images/hoian_D2.jpg', '../images/hoian_D2_1.jpg', '../images/hoian_D2_2.jpg', '../images/hoian_D2_3.jpg']
            ],
            [
                'day' => 'Day 3',
                'description' => "Embark on a half-day excursion to My Son Sanctuary, a mystical complex of ancient Hindu temples hidden in the jungle, and a recognized UNESCO World Heritage Site. Alternatively, explore the rural charm of Hoi An with a bicycle tour through rice fields and the serene coconut forests of Cam Thanh Village, offering a glimpse into local life.",
                'images' => ['../images/hoian_D3.jpg', '../images/hoian_D3_1.jpg', '../images/hoian_D3_2.jpg', '../images/hoian_D3_3.jpg']
            ]
        ],         
        'accommodation' => 'Hoi An offers a variety of stays, from charming homestays to boutique resorts near the beach or the old town.',
        'cuisine' => [
            [
                'name' => 'Cao Lau (pork noodle with herbs)',
                'image' => '../images/hoian_C1.jpg'
            ],
            [
                'name' => 'White rose dumplings (bánh bao bánh vạc)',
                'image' => '../images/hoian_C2.jpg'
            ],
            [
                'name' => 'Quang noodles (mì Quảng)',
                'image' => '../images/hoian_C3.jpg'
            ],
            [
                'name' => 'Hoi An chicken rice (cơm gà Hội An)',
                'image' => '../images/hoian_C4.jpg'
            ]
        ],
        'shopping' => 'Hoi An is famous for custom-tailored clothes, leather goods, silk lanterns, and handmade ceramics. The Night Market on Nguyen Hoang Street is a great place to shop and experience local culture.',
        'tips' => 'Wear comfortable shoes for walking in the old town. Bargaining is common in markets. Watch out for sudden rain showers—bring an umbrella or raincoat. Respect local customs and dress modestly when visiting temples or historical sites.'
    ],
    
    'hcm' => [
        'title' => 'Ho Chi Minh Travel',
        'about_title' => 'About Ho Chi Minh City',
        'about_content' => 'Located in Southern Vietnam, Ho Chi Minh City is the nation’s largest and most vibrant metropolis. As a bustling economic and cultural center, the city plays a vital role as a transportation hub, linking southern provinces and serving as a major international gateway. With its dynamic skyline, historical landmarks, and energetic street life, Ho Chi Minh City offers a compelling mix of tradition and modernity. From French colonial architecture and bustling markets to world-class dining and expanding infrastructure, the city continues to drive Vietnam’s rapid urban and economic development.',
        'coordinates' => ['latitude' => 10.8231, 'longitude' => 106.6297],
        'season_info' => [
            'good_months' => [12, 1, 2, 3, 4, 5],
            'ok_months' => [11, 6],
            'bad_months' => [7, 8, 9, 10],
            'notes' => 'Dry season (Dec-May) perfect for sightseeing with comfortable temperatures. Wet season (Jun-Nov) brings daily afternoon showers but cooler evenings. Avoid Tet holiday when city empties. Year-round tropical climate.'
        ],
        'transportation' => [
            'The main airport is Tan Son Nhat International Airport, only 20 minutes by taxi from downtown. You can also travel by train from the North via <a href="http://vetau.com.vn" target="_blank">vetau.com.vn</a> or by inter-provincial buses like Mai Linh Express.',
            'Within the city, options include taxis, buses, and motorbike taxis. Bus fares are budget-friendly.'
        ],
        'best_time' => [
            'HCMC has two seasons: dry (Dec-May) and rainy (Jun-Nov). You can visit year-round, but avoid Tet (Lunar New Year) as the city quiets down with people returning to their hometowns.',
            'During Christmas and other festivals, the city is lively with lights and celebrations.'
        ],
        'places' => [
            [
                'day' => 'Day 1',
                'description' => "Dive into the heart of Ho Chi Minh City with a walking tour around District 1. Explore iconic landmarks such as Ben Thanh Market, Notre-Dame Cathedral, the Central Post Office, and the elegant Opera House. In the afternoon, unwind at the leafy Tao Dan Park, visit Turtle Lake – a local hangout spot, and browse local flavors at Tan Dinh Market. In the evening, venture to District 5’s vibrant Chinatown to discover temples, herbal shops, and traditional street food.",
                'images' => ['../images/hcm_D1.jpg', '../images/hcm_D1_1.jpg', '../images/hcm_D1_2.jpg', '../images/hcm_D1_3.jpg']
            ],
            [
                'day' => 'Day 2',
                'description' => "Take a journey to the Cu Chi Tunnels, a vast underground network used during the Vietnam War — visit either Ben Dinh or Ben Duoc sites. Along the way, stop by the 18 Betel Nut Villages for a glimpse into rural life. In the afternoon, explore the modern urban charm of Phu My Hung and shop at Crescent Mall. In the evening, unwind with a scenic dinner cruise on the Saigon River, watching the city lights sparkle from the water.",
                'images' => ['../images/hcm_D2.jpg', '../images/hcm_D2_1.jpg', '../images/hcm_D2_2.jpg', '../images/hcm_D2_3.jpg']
            ],
            [
                'day' => 'Day 3',
                'description' => "Tailor your final day based on your interests. For family fun and cultural immersion, head to Suoi Tien or Dam Sen Theme Park. Prefer nature? Relax at the lush Binh Quoi Tourist Village or venture farther to Can Gio Mangrove Forest, a UNESCO-listed biosphere reserve offering boat tours, monkey forests, and seafood by the coast.",
                'images' => ['../images/hcm_D3.jpg', '../images/hcm_D3_1.jpg', '../images/hcm_D3_2.jpg', '../images/hcm_D3_3.jpg']
            ]
        ],
        'accommodation' => 'Stay in District 1 for walkability and nightlife. Recommended hotels include Aries Ben Thanh, Phung Hoang Gold Palace, and Ava Saigon 2.',
        'cuisine' => [
            [
                'name' => 'Banh Mi Hoa Ma (Cao Thang St)',
                'image' => '../images/hcm_C1.jpg'
            ],
            [
                'name' => 'Banh Mi Huynh Hoa (Le Thi Rieng St)',
                'image' => '../images/hcm_C2.jpg'
            ],
            [
                'name' => 'Com tam (broken rice with grilled pork, egg, and pickles)',
                'image' => '../images/hcm_C3.jpg'
            ],
            [
                'name' => 'Hu tieu Nam Vang (Southern Vietnamese noodle soup with pork and shrimp)',
                'image' => '../images/hcm_C4.jpg'
            ]
        ],
        'shopping' => 'Buy tropical fruits like mango, star apple, and green pomelo. Other great souvenirs: coffee, Soc Trang durian cakes, cashew nuts.',
        'tips' => 'Most restaurants don\'t overcharge. Bargain when shopping in local markets, especially Ben Thanh.'
    ],
    
    'hagiang' => [
        'title' => 'Ha Giang Travel',
        'about_title' => 'About Ha Giang',
        'about_content' => 'Ha Giang, Vietnam’s northernmost province, is a captivating destination celebrated for its dramatic mountain scenery, winding highland passes, and rich tapestry of ethnic minority cultures. Characterized by towering limestone peaks, deep valleys, and terraced rice fields carved into steep hillsides, the region offers an awe-inspiring landscape that remains largely untouched by mass tourism. Home to communities such as the Hmong, Tay, and Dao, Ha Giang provides a unique cultural immersion through traditional markets, festivals, and colorful attire. Ideal for adventurous travelers, it promises unforgettable off-the-beaten-path experiences along routes like the legendary Ma Pi Leng Pass and the Dong Van Karst Plateau Geopark.',
        'coordinates' => ['latitude' => 22.8230, 'longitude' => 104.9784],
        'season_info' => [
            'good_months' => [3, 4, 5, 9, 10, 11],
            'ok_months' => [1, 2, 12],
            'bad_months' => [6, 7, 8],
            'notes' => 'Spring (Mar-May) and autumn (Sep-Nov) perfect for motorbike tours with clear skies. October best for buckwheat flowers. Summer rainy season dangerous for mountain roads. Winter can be very cold but offers clear mountain views.'
        ],
        'transportation' => [
            'Ha Giang is about 300 km from Hanoi. You can travel by sleeper bus (8–10 hours) from Hanoi to Ha Giang City. From there, motorbike rental is the most popular and flexible way to explore the region.',
            'For those less comfortable riding, local tours with experienced drivers are available. Road conditions can be challenging, so drive carefully.'
        ],
        'best_time' => [
            'The best time to visit Ha Giang is from September to November (buckwheat flower season) and March to May (pleasant weather and blooming season). Avoid the rainy season (June–August) due to landslides and slippery roads.',
            'October and November are especially scenic, with blooming buckwheat flowers covering the mountain slopes.'
        ],
        'places' => [
            [
                'day' => 'Day 1',
                'description' => "Begin your Ha Giang Loop adventure by arriving in Ha Giang City and heading north through winding mountain roads. Stop at Quan Ba Heaven Gate for panoramic views and admire the unique Twin Mountains nestled in the valley below. Continue the scenic drive and spend the night in either Yen Minh or Dong Van, experiencing the charm of a northern highland town.",
                'images' => ['../images/hagiang_D1.jpg', '../images/hagiang_D1_1.jpg', '../images/hagiang_D1_2.jpg', '../images/hagiang_D1_3.jpg']
            ],
            [
                'day' => 'Day 2',
                'description' => "Explore Dong Van Old Quarter, a blend of ancient H’Mong architecture and French colonial influence. Continue to Lung Cu Flagpole, proudly marking Vietnam’s northernmost point with sweeping views over the borderlands. Along the way, visit traditional H’Mong villages to learn about local culture, crafts, and customs. Stay overnight in Dong Van town, surrounded by limestone karsts and mountain mist.",
                'images' => ['../images/hagiang_D2.jpg', '../images/hagiang_D2_1.jpg', '../images/hagiang_D2_2.jpg', '../images/hagiang_D2_3.jpg']
            ],
            [
                'day' => 'Day 3',
                'description' => "Journey through the legendary Ma Pi Leng Pass — one of the most dramatic and breathtaking mountain passes in Vietnam. Stop at skywalk viewpoints overlooking the emerald Nho Que River far below. Continue to Meo Vac, a quiet town known for its ethnic markets and stunning landscapes. Return to Ha Giang City or extend your adventure to Du Gia, a hidden gem with waterfalls, rice terraces, and authentic homestay experiences in the heart of nature.",
                'images' => ['../images/hagiang_D3.jpg', '../images/hagiang_D3_1.jpg', '../images/hagiang_D3_2.jpg', '../images/hagiang_D3_3.jpg']
            ]
        ],
        'accommodation' => 'Ha Giang offers cozy homestays in ethnic villages and small hotels in towns. Dong Van and Meo Vac are popular for overnight stays.',
        'cuisine' => [
            [
                'name' => 'Thang co (horse meat stew – traditional H\'Mong dish)',
                'image' => '../images/hagiang_C1.jpg'
            ],
            [
                'name' => 'Au tau porridge (cháo ấu tẩu)',
                'image' => '../images/hagiang_C2.jpg'
            ],
            [
                'name' => 'Men men (steamed cornmeal)',
                'image' => '../images/hagiang_C3.jpg'
            ],
            [
                'name' => 'Smoked buffalo meat (thịt trâu gác bếp)',
                'image' => '../images/hagiang_C4.jpg'
            ]
        ],
        'shopping' => 'Buy handmade brocade textiles, ethnic clothing, buckwheat flower honey, and herbal tea. Weekly markets like Dong Van and Meo Vac Sunday markets are perfect for local products and cultural interaction.',
        'tips' => 'Bring warm clothing, especially in winter. Roads are steep and narrow—drive cautiously. Be respectful of local customs and ask for permission before taking photos. Cell signal can be weak in remote areas—download offline maps in advance.'
    ],
    
    'dalat' => [
        'title' => 'Da Lat Travel',
        'about_title' => 'About Da Lat',
        'about_content' => 'Da Lat, nestled in the Central Highlands of Vietnam, is affectionately known as the "City of Eternal Spring" for its temperate climate, rolling pine-covered hills, and colorful flower gardens that bloom year-round. Originally established as a French colonial hill station, the city retains much of its European charm through elegant villas, cobblestone streets, and a distinctly romantic ambiance. Surrounded by misty valleys, serene lakes, and waterfalls, Da Lat offers a peaceful retreat from the tropical heat of the lowlands. It remains a favored destination for honeymooners, nature lovers, and those seeking a harmonious blend of culture, history, and natural beauty.',
        'coordinates' => ['latitude' => 11.9404, 'longitude' => 108.4583],
        'season_info' => [
            'good_months' => [11, 12, 1, 2, 3],
            'ok_months' => [4, 5, 10],
            'bad_months' => [6, 7, 8, 9],
            'notes' => 'Cool and dry Nov-Mar perfect for flower gardens and outdoor activities. Flower Festival in December (biennial). Rainy season Jun-Sep can be foggy but offers green landscapes. Year-round spring-like climate.'
        ],
        'transportation' => [
            'You can reach Da Lat by plane via Lien Khuong Airport, located about 30 km from the city center. Buses from Ho Chi Minh City, Nha Trang, or nearby cities are also available. The roads to Da Lat offer scenic views of mountain passes and valleys.',
            'Within the city, taxis, motorbike rentals, and electric carts are common ways to get around. Many attractions are within a short drive or walk from the city center.'
        ],
        'best_time' => [
            'The ideal time to visit Da Lat is from November to March, during the dry season when the weather is cool and flowers bloom. The Flower Festival, held every two years in December, is a major event worth attending.',
            'Spring (late January to March) features cherry blossoms and beautiful garden displays.'
        ],
        'places' => [
            [
                'day' => 'Day 1',
                'description' => "Start your Da Lat journey with a peaceful walk around Xuan Huong Lake, the heart of the city’s charm. Visit Da Lat Flower Park to admire vibrant blooms and take photos at the iconic Lam Vien Square. Wander through the French Quarter with its elegant colonial villas, and unwind at a hillside café while enjoying panoramic views and the cool highland breeze.",
                'images' => ['../images/dalat_D1.jpg', '../images/dalat_D1_1.jpg', '../images/dalat_D1_2.jpg', '../images/dalat_D1_3.jpg']
            ],
            [
                'day' => 'Day 2',
                'description' => "Begin the day with an exciting alpine coaster ride down to Datanla Waterfall. Continue to the surreal and artistic Crazy House, a one-of-a-kind architectural wonder. Then, take a scenic cable car ride to Truc Lam Zen Monastery, where you can enjoy tranquil views over Tuyen Lam Lake. In the evening, immerse yourself in the lively atmosphere of the Da Lat Night Market — perfect for street food, souvenirs, and local culture.",
                'images' => ['../images/dalat_D2.jpg', '../images/dalat_D2_1.jpg', '../images/dalat_D2_2.jpg', '../images/dalat_D2_3.jpg']
            ],
            [
                'day' => 'Day 3',
                'description' => "Head to Langbiang Mountain for sweeping views of the Central Highlands and an opportunity to learn about the culture of the indigenous K'Ho people. Depending on your interests, make optional stops at the whimsical Clay Tunnel, the romantic Valley of Love, or the charming pink-hued Domaine de Marie Church before concluding your Da Lat adventure.",
                'images' => ['../images/dalat_D3.jpg', '../images/dalat_D3_1.jpg', '../images/dalat_D3_2.jpg', '../images/dalat_D3_3.jpg']
            ]
        ],
        'accommodation' => 'Choose from charming homestays, French-inspired hotels, or romantic resorts with views of pine forests and valleys.',
        'cuisine' => [
            [
                'name' => 'Bánh tráng nướng (Vietnamese pizza)',
                'image' => '../images/dalat_C1.jpg'
            ],
            [
                'name' => 'Lẩu gà lá é (chicken hotpot with basil)',
                'image' => '../images/dalat_C2.jpg'
            ],
            [
                'name' => 'Soy milk at night markets',
                'image' => '../images/dalat_C3.jpg'
            ],
            [
                'name' => 'Fresh strawberries and artichoke tea',
                'image' => '../images/dalat_C4.jpg'
            ]
        ],
        'shopping' => 'Popular items include dried fruits, Da Lat wine, jam, fresh flowers, and local handicrafts. The Night Market is ideal for souvenirs and street snacks.',
        'tips' => 'Pack warm clothes, especially for evenings and early mornings. Bring cash for local markets. Watch out for foggy roads if you\'re riding a motorbike. Respect quiet zones at religious and cultural sites.'
    ],

    'danang' => [
        'title' => 'Da Nang Travel',
        'about_title' => 'About Da Nang',
        'about_content' => 'Da Nang, located on Vietnam’s central coast, is a vibrant city known for its golden beaches, iconic bridges, and a harmonious blend of tradition and modernity. The city serves as a gateway to UNESCO World Heritage Sites such as Hoi An Ancient Town and My Son Sanctuary. With attractions like the Marble Mountains, Ba Na Hills with its famous Golden Bridge, and the Son Tra Peninsula, Da Nang offers both cultural depth and natural beauty. Its friendly atmosphere, delicious cuisine, and dynamic nightlife make it one of Vietnam’s top destinations.',
        'coordinates' => ['latitude' => 16.0471, 'longitude' => 108.2068],
        'season_info' => [
            'good_months' => [2, 3, 4, 5, 8, 9],
            'ok_months' => [1, 6, 7],
            'bad_months' => [10, 11, 12],
            'notes' => 'Feb-May and Aug-Sep are ideal with pleasant weather. Jun-Jul is hot but great for beach lovers. Oct-Dec often brings heavy rains and occasional typhoons.'
        ],
        'transportation' => [
            'Da Nang International Airport connects the city with major domestic and international destinations. Trains and buses from Hanoi, Hue, and Ho Chi Minh City are also available.',
            'Within the city, taxis, Grab, and motorbike rentals are the most convenient. Cycling along the Han River and beachside boulevards is also popular.'
        ],
        'best_time' => [
            'The best time to visit Da Nang is from February to May when the weather is cool, dry, and ideal for sightseeing and beach activities.',
            'August and September are also good months, with fewer crowds compared to peak summer.'
        ],
        'places' => [
            [
                'day' => 'Day 1',
                'description' => "Start your Da Nang adventure with a visit to the Marble Mountains, exploring caves, pagodas, and viewpoints. Head to My Khe Beach for a relaxing afternoon on golden sands. In the evening, enjoy a walk along the Han River, marvel at the Dragon Bridge, and watch its fiery performance on weekends.",
                'images' => ['../images/danang_D1.jpg', '../images/danang_D1_1.jpg', '../images/danang_D1_2.jpg', '../images/danang_D1_3.jpg']
            ],
            [
                'day' => 'Day 2',
                'description' => "Take a day trip to Ba Na Hills and experience the famous Golden Bridge supported by giant stone hands. Ride the cable car, explore the French Village, and enjoy theme park activities. Return to Da Nang for sunset at Son Tra Peninsula’s Linh Ung Pagoda with its towering Lady Buddha statue.",
                'images' => ['../images/danang_D2.jpg', '../images/danang_D2_1.jpg', '../images/danang_D2_2.jpg', '../images/danang_D2_3.jpg']
            ],
            [
                'day' => 'Day 3',
                'description' => "Head south to Hoi An Ancient Town, a UNESCO World Heritage Site just 30 km away. Stroll through lantern-lit streets, visit temples, and try traditional tailoring. In the evening, return to Da Nang to explore the bustling night markets or enjoy seafood by the beach.",
                'images' => ['../images/danang_D3.jpg', '../images/danang_D3_1.jpg', '../images/danang_D3_2.jpg', '../images/danang_D3_3.jpg']
            ]
        ],
        'accommodation' => 'Choose from luxurious beach resorts, city-center hotels with river views, or boutique stays near Han Market.',
        'cuisine' => [
            [
                'name' => 'Mì Quảng (Quang-style noodles)',
                'image' => '../images/danang_C1.jpg'
            ],
            [
                'name' => 'Bánh tráng cuốn thịt heo (pork with rice paper rolls)',
                'image' => '../images/danang_C2.jpg'
            ],
            [
                'name' => 'Seafood at local beachside restaurants',
                'image' => '../images/danang_C3.jpg'
            ],
            [
                'name' => 'Bún chả cá (fishcake noodle soup)',
                'image' => '../images/danang_C4.jpg'
            ]
        ],
        'shopping' => 'Visit Han Market and Con Market for local produce, dried seafood, and souvenirs. Night markets along the riverfront are perfect for food, fashion, and handicrafts.',
        'tips' => 'Bring sunscreen for beach days. Be cautious when crossing roads as traffic can be busy. If visiting during typhoon season (Oct-Dec), check weather updates. Try to schedule weekends for the Dragon Bridge fire and water show.'
    ],

    'cantho' => [
        'title' => 'Can Tho Travel',
        'about_title' => 'About Can Tho',
        'about_content' => 'Can Tho, the largest city in the Mekong Delta, is often called the "Western Capital" of Vietnam. Known for its lush waterways, vibrant floating markets, and fertile rice fields, Can Tho captures the essence of life in the delta. Visitors are drawn to its iconic Cai Rang Floating Market, tranquil canals lined with fruit orchards, and hospitable people. The city blends traditional river culture with modern development, offering both authentic local experiences and urban comforts. It is an ideal destination to experience the charm of Vietnam’s riverine lifestyle.',
        'coordinates' => ['latitude' => 10.0452, 'longitude' => 105.7469],
        'season_info' => [
            'good_months' => [12, 1, 2, 3, 4],
            'ok_months' => [5, 11],
            'bad_months' => [6, 7, 8, 9, 10],
            'notes' => 'Dry season (Dec-Apr) is the best time to visit with clear skies and cooler weather. May-Oct is the rainy season, which can bring floods but also lush greenery and fresh fruits.'
        ],
        'transportation' => [
            'Can Tho International Airport connects to Hanoi, Da Nang, and Ho Chi Minh City with domestic flights. Buses are a common option from Ho Chi Minh City, taking about 3-4 hours via expressway.',
            'Within the city, taxis, motorbike rentals, and especially boat tours are the most popular ways to get around and explore the canals.'
        ],
        'best_time' => [
            'The ideal time to visit Can Tho is from December to April when the weather is dry and pleasant, perfect for boat trips and exploring orchards.',
            'Visiting during the rainy season offers abundant tropical fruits and lush landscapes but be prepared for showers.'
        ],
        'places' => [
            [
                'day' => 'Day 1',
                'description' => "Begin your journey at Cai Rang Floating Market, the largest in the Mekong Delta, where boats brimming with fruits and vegetables trade at dawn. Then cruise along the canals to visit local orchards and enjoy fresh tropical fruits. In the afternoon, explore Ong Pagoda and take a relaxing walk along Ninh Kieu Wharf with its scenic river views.",
                'images' => ['../images/cantho_D1.jpg', '../images/cantho_D1_1.jpg', '../images/cantho_D1_2.jpg', '../images/cantho_D1_3.jpg']
            ],
            [
                'day' => 'Day 2',
                'description' => "Visit Bang Lang Stork Sanctuary to see thousands of storks in their natural habitat. Continue to Binh Thuy Ancient House, a French-colonial style residence blending East and West architecture. In the evening, enjoy a sunset cruise or sample local dishes at riverside restaurants.",
                'images' => ['../images/cantho_D2.jpg', '../images/cantho_D2_1.jpg', '../images/cantho_D2_2.jpg', '../images/cantho_D2_3.jpg']
            ],
            [
                'day' => 'Day 3',
                'description' => "Take a day trip deeper into the Mekong Delta to experience smaller floating markets, traditional handicraft villages, and serene countryside landscapes. Return to Can Tho to relax at a café by the Hau River before concluding your trip.",
                'images' => ['../images/cantho_D3.jpg', '../images/cantho_D3_1.jpg', '../images/cantho_D3_2.jpg', '../images/cantho_D3_3.jpg']
            ]
        ],
        'accommodation' => 'Choose from riverside resorts, boutique hotels near Ninh Kieu Wharf, or homestays in lush orchards along the canals.',
        'cuisine' => [
            [
                'name' => 'Lẩu mắm (fermented fish hotpot)',
                'image' => '../images/cantho_C1.jpg'
            ],
            [
                'name' => 'Bánh xèo miền Tây (Mekong-style crispy pancake)',
                'image' => '../images/cantho_C2.jpg'
            ],
            [
                'name' => 'Hủ tiếu gõ (southern noodle soup)',
                'image' => '../images/cantho_C3.jpg'
            ],
            [
                'name' => 'Fresh tropical fruits (mango, durian, rambutan)',
                'image' => '../images/cantho_C4.jpg'
            ]
        ],
        'shopping' => 'Visit Can Tho Night Market for souvenirs, snacks, and local crafts. Floating markets are perfect for buying seasonal fruits. Traditional handicrafts and rice paper products are popular take-home items.',
        'tips' => 'Wake up early to catch the floating markets at their busiest (around 5–7 am). Bring sunscreen and hats for boat trips. Carry cash for purchases at markets. Be cautious during rainy season as canals may flood.'
    ],

    'hanoi' => [
        'title' => 'Hanoi Travel',
        'about_title' => 'About Hanoi',
        'about_content' => 'Hanoi, the capital of Vietnam, is a city where ancient traditions harmoniously blend with modern development. Known as the "City of Lakes," it features historical landmarks, tree-lined boulevards, French colonial architecture, and vibrant street life. The Old Quarter is a maze of narrow alleys bustling with shops, food stalls, and local markets. Beyond its rich history, Hanoi offers a thriving cultural scene, lively nightlife, and serves as the gateway to northern Vietnam’s iconic destinations such as Ha Long Bay, Ninh Binh, and Sapa.',
        'coordinates' => ['latitude' => 21.0285, 'longitude' => 105.8542],
        'season_info' => [
            'good_months' => [10, 11, 12, 3, 4],
            'ok_months' => [1, 2, 9],
            'bad_months' => [5, 6, 7, 8],
            'notes' => 'Oct–Dec and Mar–Apr are the best times with cool, pleasant weather. Jan–Feb can be chilly and misty but atmospheric during Tet (Lunar New Year). May–Aug is hot and humid, with heavy rains in summer.'
        ],
        'transportation' => [
            'Noi Bai International Airport connects Hanoi to major global and domestic destinations. Trains and buses link the city with other provinces.',
            'Within Hanoi, taxis, Grab, cyclos, and motorbike rentals are common. Walking around the Old Quarter is the best way to explore its charm.'
        ],
        'best_time' => [
            'The best times to visit Hanoi are in autumn (Oct–Nov) when the weather is cool, dry, and the city is at its most romantic, and in spring (Mar–Apr) with blooming flowers and comfortable temperatures.',
            'Winter (Dec–Feb) is also popular for cultural festivals, though it can be cold and misty.'
        ],
        'places' => [
            [
                'day' => 'Day 1',
                'description' => "Begin with a walk around Hoan Kiem Lake and Ngoc Son Temple, the spiritual heart of Hanoi. Explore the Old Quarter with its bustling streets, traditional guilds, and street food delights. In the afternoon, visit the Temple of Literature, Vietnam’s first university, followed by a cyclo ride around the French Quarter. End the day with a water puppet show, a unique Vietnamese art form.",
                'images' => ['../images/hanoi_D1.jpg', '../images/hanoi_D1_1.jpg', '../images/hanoi_D1_2.jpg', '../images/hanoi_D1_3.jpg']
            ],
            [
                'day' => 'Day 2',
                'description' => "Start with Ho Chi Minh Mausoleum, One Pillar Pagoda, and the Presidential Palace area. Continue to the Vietnam Museum of Ethnology for insights into the country’s diverse cultures. In the evening, relax with egg coffee at a rooftop café overlooking Hoan Kiem Lake.",
                'images' => ['../images/hanoi_D2.jpg', '../images/hanoi_D2_1.jpg', '../images/hanoi_D2_2.jpg', '../images/hanoi_D2_3.jpg']
            ],
            [
                'day' => 'Day 3',
                'description' => "Take a day trip to nearby attractions: the Perfume Pagoda with its scenic boat ride and cable car, or Bat Trang Ceramic Village to learn traditional pottery-making. Return to Hanoi for shopping at Dong Xuan Market and enjoy the vibrant nightlife around Ta Hien beer street.",
                'images' => ['../images/hanoi_D3.jpg', '../images/hanoi_D3_1.jpg', '../images/hanoi_D3_2.jpg', '../images/hanoi_D3_3.jpg']
            ]
        ],
        'accommodation' => 'Stay in boutique hotels in the Old Quarter, luxury hotels in the French Quarter, or modern apartments around Tay Ho (West Lake).',
        'cuisine' => [
            [
                'name' => 'Phở Hà Nội (Hanoi beef noodle soup)',
                'image' => '../images/hanoi_C1.jpg'
            ],
            [
                'name' => 'Bún chả (grilled pork with noodles)',
                'image' => '../images/hanoi_C2.jpg'
            ],
            [
                'name' => 'Chả cá Lã Vọng (grilled turmeric fish with dill)',
                'image' => '../images/hanoi_C3.jpg'
            ],
            [
                'name' => 'Cà phê trứng (egg coffee)',
                'image' => '../images/hanoi_C4.jpg'
            ]
        ],
        'shopping' => 'Dong Xuan Market and the Old Quarter are best for handicrafts, silk, and souvenirs. Weekend night markets offer local goods and street food. Hang Gai Street is famous for silk products.',
        'tips' => 'Wake up early to enjoy the peaceful side of Hanoi at Hoan Kiem Lake. Bargain politely at markets. Be mindful of traffic when crossing streets. Try local street food but choose busy stalls for freshness.'
    ]
];

// Check if destination exists, default to hcm
if (!isset($travelTips[$destination])) {
    $destination = 'hcm';
}

$tip = $travelTips[$destination];

// Get current year for calendar
$currentYear = date('Y');
$currentMonth = (int)date('n');

// Month names
$monthNames = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

function getMonthClass($month, $seasonInfo, $currentMonth) {
    $class = '';
    if (in_array($month, $seasonInfo['good_months'])) {
        $class = 'good-season';
    } elseif (in_array($month, $seasonInfo['ok_months'])) {
        $class = 'ok-season';
    } else {
        $class = 'bad-season';
    }
    
    if ($month == $currentMonth) {
        $class .= ' current-month';
    }
    
    return $class;
}

function getMonthStatus($month, $seasonInfo) {
    if (in_array($month, $seasonInfo['good_months'])) {
        return 'Best';
    } elseif (in_array($month, $seasonInfo['ok_months'])) {
        return 'Good';
    } else {
        return 'Avoid';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tip['title']) ?></title>
    <link rel="stylesheet" href="../css/traveltips.css">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    
    <!-- Leaflet CSS and JS for maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>

<?php include __DIR__ . '/../header.php'; ?>

<body>
    <main class="container">
        <section class="section">
            <h1 class="page-title"><?= htmlspecialchars($tip['about_title']) ?></h1>
            <p class="about-content"><?= $tip['about_content'] ?></p>
        </section>

        <section class="section">
            <h2><i class="fas fa-route"></i>Transportation</h2>
            <div class="content-grid">
                <?php foreach ($tip['transportation'] as $transport): ?>
                    <div class="transport-item">
                        <p><?= $transport ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section calendar-section">
            <h2><i class="fas fa-calendar-alt"></i>Best Time to Visit</h2>
            <div class="calendar-header">
                <div class="calendar-year"><?= $currentYear ?></div>
                <p>Interactive calendar showing the best months to visit</p>
            </div>
            <div class="calendar-grid">
                <?php for ($month = 1; $month <= 12; $month++): ?>
                    <div class="month-item <?= getMonthClass($month, $tip['season_info'], $currentMonth) ?>" 
                         data-month="<?= $month ?>">
                        <div class="month-name"><?= $monthNames[$month] ?></div>
                        <div class="month-status"><?= getMonthStatus($month, $tip['season_info']) ?></div>
                    </div>
                <?php endfor; ?>
            </div>
            <div class="calendar-legend">
                <div class="legend-item">
                    <div class="legend-dot good"></div>
                    <span>Best Time</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot ok"></div>
                    <span>Good Time</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot bad"></div>
                    <span>Avoid</span>
                </div>
            </div>
            <div class="content-grid" style="margin-top: 2rem;">
                <?php foreach ($tip['best_time'] as $time): ?>
                    <div class="time-item">
                        <p><?= $time ?></p>
                    </div>
                <?php endforeach; ?>
                <div class="time-item">
                    <p><strong>Seasonal Notes:</strong> <?= $tip['season_info']['notes'] ?></p>
                </div>
            </div>
        </section>

        <section class="section">
            <h2><i class="fas fa-map-marker-alt"></i>Places to Visit</h2>
            <div class="places-container">
                <?php foreach ($tip['places'] as $place): ?>
                    <?php if (is_array($place) && isset($place['day'])): ?>
                        <div class="place-card">
                            <div class="place-header">
                                <span class="place-day"><?= htmlspecialchars($place['day']) ?></span>
                            </div>
                            <div class="place-content">
                                <div class="place-text">
                                    <p><?= $place['description'] ?></p>
                                </div>
                                <?php if (isset($place['images']) && count($place['images']) > 0): ?>
                                    <div class="place-image">
                                        <?php if (count($place['images']) == 1): ?>
                                            <img src="<?= htmlspecialchars($place['images'][0]) ?>" alt="<?= htmlspecialchars($place['day']) ?>">
                                        <?php else: ?>
                                            <div class="photo-carousel" data-images='<?= json_encode($place['images']) ?>'>
                                                <div class="carousel-container">
                                                    <div class="carousel-track">
                                                        <?php foreach ($place['images'] as $index => $image): ?>
                                                            <div class="carousel-slide">
                                                                <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($place['day']) ?> - Photo <?= $index + 1 ?>">
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <button class="carousel-controls carousel-prev" aria-label="Previous image">‹</button>
                                                    <button class="carousel-controls carousel-next" aria-label="Next image">›</button>
                                                    <div class="carousel-indicators">
                                                        <?php foreach ($place['images'] as $index => $image): ?>
                                                            <div class="carousel-dot <?= $index === 0 ? 'active' : '' ?>" data-slide="<?= $index ?>"></div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="place-simple">
                            <p><?= $place ?></p>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="dual-section">
            <section class="section accommodation-section">
                <h2><i class="fas fa-bed"></i>Accommodation</h2>
                <div class="accommodation-content">
                    <p><?= $tip['accommodation'] ?></p>
                    
                    <!-- Mini Map for Accommodation -->
                    <div class="mini-map-container">
                        <div id="accommodationMap" class="mini-map"></div>
                        <button class="map-expand-btn" onclick="openMapModal()">
                            <i class="fas fa-expand"></i> View Large Map
                        </button>
                    </div>
                </div>
            </section>

            <section class="section cuisine-section">
                <h2><i class="fas fa-utensils"></i>Local Cuisine</h2>
                <div class="cuisine-grid">
                    <?php if (isset($tip['cuisine'][0]) && is_array($tip['cuisine'][0])): ?>
                        <?php foreach ($tip['cuisine'] as $dish): ?>
                            <div class="cuisine-card">
                                <?php if (isset($dish['image'])): ?>
                                    <div class="cuisine-image">
                                        <img src="<?= htmlspecialchars($dish['image']) ?>" alt="<?= htmlspecialchars($dish['name']) ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="cuisine-name">
                                    <p><?= htmlspecialchars($dish['name']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="cuisine-list">
                            <?php foreach ($tip['cuisine'] as $dish): ?>
                                <div class="cuisine-item-simple">
                                    <p><?= $dish ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <section class="section">
            <h2><i class="fas fa-shopping-bag"></i>Shopping & Souvenirs</h2>
            <p><?= $tip['shopping'] ?></p>
        </section>

        <section class="section tips-section">
            <h2><i class="fas fa-lightbulb"></i>Tips & Cautions</h2>
            <div class="tips-content">
                <p><?= $tip['tips'] ?></p>
            </div>
        </section>
    </main>

    <!-- Map Modal -->
    <div id="mapModal" class="map-modal">
        <div class="map-modal-content">
            <div class="map-modal-header">
                <h3><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($tip['about_title']) ?> - Location & Hotels</h3>
                <button class="map-close" onclick="closeMapModal()">&times;</button>
            </div>
            <div id="largeMap" class="large-map"></div>
        </div>
    </div>
    
    <script>
        // Photo carousel functionality
        document.addEventListener('DOMContentLoaded', function() {
            initializeCarousels();
            initializeMap();
        });

        function initializeCarousels() {
            const carousels = document.querySelectorAll('.photo-carousel');
            
            carousels.forEach(carousel => {
                const track = carousel.querySelector('.carousel-track');
                const slides = carousel.querySelectorAll('.carousel-slide');
                const prevBtn = carousel.querySelector('.carousel-prev');
                const nextBtn = carousel.querySelector('.carousel-next');
                const dots = carousel.querySelectorAll('.carousel-dot');
                
                let currentSlide = 0;
                let autoSlideInterval;
                
                function updateCarousel() {
                    const translateX = -currentSlide * 100;
                    track.style.transform = `translateX(${translateX}%)`;
                    
                    // Update dots
                    dots.forEach((dot, index) => {
                        dot.classList.toggle('active', index === currentSlide);
                    });
                }
                
                function nextSlide() {
                    currentSlide = (currentSlide + 1) % slides.length;
                    updateCarousel();
                }
                
                function prevSlide() {
                    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                    updateCarousel();
                }
                
                function goToSlide(index) {
                    currentSlide = index;
                    updateCarousel();
                }
                
                function startAutoSlide() {
                    autoSlideInterval = setInterval(nextSlide, 4000);
                }
                
                function stopAutoSlide() {
                    clearInterval(autoSlideInterval);
                }
                
                // Event listeners
                nextBtn.addEventListener('click', () => {
                    stopAutoSlide();
                    nextSlide();
                    startAutoSlide();
                });
                
                prevBtn.addEventListener('click', () => {
                    stopAutoSlide();
                    prevSlide();
                    startAutoSlide();
                });
                
                dots.forEach((dot, index) => {
                    dot.addEventListener('click', () => {
                        stopAutoSlide();
                        goToSlide(index);
                        startAutoSlide();
                    });
                });
                
                // Pause on hover
                carousel.addEventListener('mouseenter', stopAutoSlide);
                carousel.addEventListener('mouseleave', startAutoSlide);
                
                // Start auto-slide
                startAutoSlide();
            });
        }

        // Map functionality
        let miniMap, largeMap;
        const coordinates = <?= json_encode($tip['coordinates']) ?>;

        function initializeMap() {
            if (!coordinates) return;

            // Initialize mini map
            miniMap = L.map('accommodationMap', {
                zoomControl: false,
                scrollWheelZoom: false,
                doubleClickZoom: false,
                boxZoom: false,
                keyboard: false,
                dragging: false
            }).setView([coordinates.latitude, coordinates.longitude], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(miniMap);

            // Add marker
            const mainIcon = L.divIcon({
                className: 'custom-marker',
                html: '<div class="marker-pin main-pin">📍</div>',
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });

            L.marker([coordinates.latitude, coordinates.longitude], {
                icon: mainIcon
            }).addTo(miniMap).bindPopup('<?= htmlspecialchars($tip['about_title']) ?>');
        }

        function openMapModal() {
            document.getElementById('mapModal').style.display = 'flex';
            
            setTimeout(() => {
                if (largeMap) {
                    largeMap.remove();
                }
                
                largeMap = L.map('largeMap').setView([coordinates.latitude, coordinates.longitude], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(largeMap);

                // Add main location marker
                const mainIcon = L.divIcon({
                    className: 'custom-marker',
                    html: '<div class="marker-pin main-pin">📍</div>',
                    iconSize: [40, 40],
                    iconAnchor: [20, 40]
                });

                L.marker([coordinates.latitude, coordinates.longitude], {
                    icon: mainIcon
                }).addTo(largeMap).bindPopup('<b><?= htmlspecialchars($tip['about_title']) ?></b><br>Main destination area');

                // Add some sample hotel markers around the area
                const hotelIcon = L.divIcon({
                    className: 'custom-marker',
                    html: '<div class="marker-pin nearby-pin">🏨</div>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 30]
                });

                // Sample nearby hotels (you can replace with real data)
                const sampleHotels = [
                    { lat: coordinates.latitude + 0.01, lng: coordinates.longitude + 0.01, name: 'Luxury Hotel A' },
                    { lat: coordinates.latitude - 0.01, lng: coordinates.longitude + 0.01, name: 'Budget Hotel B' },
                    { lat: coordinates.latitude + 0.01, lng: coordinates.longitude - 0.01, name: 'Boutique Hotel C' }
                ];

                sampleHotels.forEach(hotel => {
                    L.marker([hotel.lat, hotel.lng], {
                        icon: hotelIcon
                    }).addTo(largeMap).bindPopup(`<b>${hotel.name}</b><br>Accommodation option`);
                });

            }, 100);
        }

        function closeMapModal() {
            document.getElementById('mapModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('mapModal');
            if (event.target === modal) {
                closeMapModal();
            }
        });

        // Calendar interactivity
        document.querySelectorAll('.month-item').forEach(item => {
            item.addEventListener('click', function() {
                const month = this.dataset.month;
                const monthName = this.querySelector('.month-name').textContent;
                const status = this.querySelector('.month-status').textContent;
                
                alert(`${monthName} <?= $currentYear ?>\nTravel Status: ${status}\n\n<?= addslashes($tip['season_info']['notes']) ?>`);
            });
        });
    </script>
    
<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>