<style>
    .form-title {
        font-family: "IBM Plex Sans Arabic" !important;
        font-style: normal;
        font-weight: 700;
        /* font-size: 36px; */
        line-height: 42px;
        color: #fff;
    }

    .hero {
        width: 100%;
        height: 50vh;
        /* background-color: #ED1088; */
        background-image: URL('https://support.anasacademy.uk/resources/views/images/StoriesBG.webp');
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        display: flex;
        flex-direction: column;
        flex-wrap: nowrap;
        justify-content: center;
        align-items: stretch;
    }

    @media(max-width:768px) {
        .hero {
            height: 50vh;
        }
    }
</style>

<header class="hero cart-banner position-relative">
    <section class="container hero-title text-white">
        <h1 class='form-title font-36'>{{ trans('application_form.acceptance_form') }}</h1>
    </section>
</header>
