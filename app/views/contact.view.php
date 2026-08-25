<?php

$title = "Contact";

include "includes/header.php";
?>

<link rel="stylesheet" href="<?= asset_css('contact.css') ?>">

<div class="body">
    <?php include "includes/navigation.php"; ?>

    <main class="main-content" id="main-content" tabindex="-1">
        <h1>Contact Us</h1>

        <div class="contact-grid">
            <div class="contact-card">
                <div class="card-header">
                    <img src="<?= IMGPATH ?>/bsu.webp" alt="" class="card-logo">
                    <h2>Benguet State University - La Trinidad Campus</h2>
                </div>

                <dl class="contact-details">
                    <div class="contact-row">
                        <dt>
                            <svg class="row-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <use href="#location-icon" />
                            </svg>Address
                        </dt>
                        <dd>La Trinidad, Benguet, 2601 Philippines</dd>
                    </div>
                </dl>
            </div>

            <div class="contact-card">
                <div class="card-header">
                    <img src="<?= IMGPATH ?>/ccard.webp" alt="" class="card-logo">
                    <h2>Cordillera Center for Research and Development</h2>
                </div>

                <dl class="contact-details">
                    <div class="contact-row">
                        <dt>
                            <svg class="row-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <use href="#location-icon" />
                            </svg>Address
                        </dt>
                        <dd>CCARD Bldg., CVM Compound, KM.5, La Trinidad, Benguet, 2601 Philipppines</dd>
                    </div>
                    <div class="contact-row">
                        <dt>
                            <svg class="row-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <use href="#phone-icon" />
                            </svg>Phone
                        </dt>
                        <dd>+63 998 281 8950</dd>
                    </div>
                    <div class="contact-row">
                        <dt>
                            <svg class="row-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <use href="#email-icon" />
                            </svg>Email
                        </dt>
                        <dd><a href="mailto:ccard@bsu.edu.ph" class="underlined">ccard@bsu.edu.ph</a></dd>
                    </div>
                    <div class="contact-row">
                        <dt>
                            <svg class="row-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <use href="#facebook-icon" />
                            </svg>Facebook
                        </dt>
                        <dd><a href="https://www.facebook.com/profile.php?id=100083273710247" target="_blank" class="underlined">BSU - Cordillera Center for Animal Research & Development</a></dd>
                    </div>

                    <div class="notable-people">
                        <div>
                            <span class="person-name">Dr. Ana Mendoza</span>
                            <span class="person-role">Director, BSU-CCARD</span>
                        </div>

                        <dt>
                            <svg class="row-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <use href="#email-icon" />
                            </svg> <a href="mailto:ab.mendoza@gmail.com" class="underlined">ab.mendoza@gmail.com</a>
                        </dt>
                    </div>
                </dl>
            </div>

            <div class="contact-card">
                <div class="card-header">
                    <img src="<?= IMGPATH ?>/bai.webp" alt="" class="card-logo">
                    <h2>Bureau of Animal Industry</h2>
                </div>

                <dl class="contact-details">
                    <div class="contact-row">
                        <dt>
                            <svg class="row-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <use href="#location-icon" />
                            </svg>Address
                        </dt>
                        <dd>BPI Compound, Guisad, Baguio City, Benguet</dd>
                    </div>
                    <div class="contact-row">
                        <dt>
                            <svg class="row-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <use href="#phone-icon" />
                            </svg>Phone
                        </dt>
                        <div>
                            <dd>(074) 444-9872</dd>
                            <dd>+63 956 659 5110</dd>
                        </div>
                    </div>
                    <div class="contact-row">
                        <dt>
                            <svg class="row-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <use href="#email-icon" />
                            </svg>Email
                        </dt>
                        <div>
                            <dd>
                                <a href="mailto:regulatorydivision.car@gmail.com" class="underlined">regulatorydivision.car@gmail.com</a>
                            </dd>
                            <dd>
                                <a href="mailto:livestock.cordillera@gmail.com" class="underlined">livestock.cordillera@gmail.com</a>
                            </dd>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </main>
</div>

<?php include "includes/footer.php"; ?>