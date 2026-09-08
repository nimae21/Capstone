<footer>
    <div class="container footer-grid">
        <div class="footer-col">
            <div class="logo" style="margin-bottom: 1rem; justify-content: flex-start;">
    <img src="{{ asset('images/achilles logo.png') }}" alt="Achilles Logo" class="logo-image">
    
</div>
            <p>Authentic footwear store<br>for the relentless.</p>
            <div class="social-icons">
                <button type="button" class="social-button" data-info="facebook" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></button>
                <button type="button" class="social-button" data-info="x-twitter" aria-label="X"><i class="fab fa-x-twitter" aria-hidden="true"></i></button>
                <button type="button" class="social-button" data-info="tiktok" aria-label="TikTok"><i class="fab fa-tiktok" aria-hidden="true"></i></button>
            </div>
        </div>
        <div class="footer-col">
            <h5>EXPLORE</h5>
            <ul>
                <li><a href="{{ route('men') }}">Men's</a></li>
                <li><a href="{{ route('women') }}">Women's</a></li>
                <li><a href="{{ route('kids') }}">Kids</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h5>SUPPORT</h5>
            <ul>
                <li><a href="#info-modal" data-info="help">Help Center</a></li>
                <li><a href="#info-modal" data-info="size">Size Guide</a></li>
                <li><a href="#info-modal" data-info="authenticity">Authenticity Check</a></li>
                <li><a href="#info-modal" data-info="tracking">Track Order</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h5>COMPANY</h5>
            <ul>
                <li><a href="#info-modal" data-info="about">About Achilles</a></li>
                <li><a href="#info-modal" data-info="sustainability">Sustainability</a></li>
                <li><a href="#info-modal" data-info="press">Press</a></li>
            </ul>
        </div>
    </div>
    <div class="copyright container">
        <i class="far fa-copyright"></i> {{ date('Y') }} Achilles — Premium Footwear Store.
    </div>
</footer>
<style>
        footer .logo-image { display: block; height: 50px; width: auto; max-width: 220px; object-fit: contain; }
        footer .footer-col h5 { color: #dc2626; margin-bottom: 1rem; }
        .social-button { border: 0; background: transparent; font-size: inherit; cursor: pointer; }
        :focus-visible { outline: 3px solid #E50914; outline-offset: 5px; }
        body.modal-open { overflow: hidden; }
        .landing-modal { margin: auto; width: min(520px, calc(100% - 2rem)); max-height: calc(100dvh - 2rem); overflow-y: auto; border: 0; border-radius: 24px; padding: 2.5rem 2rem 2rem; color: #1a1a1f; box-shadow: 0 24px 80px #0003; }
        .landing-modal::backdrop { background: rgba(15, 15, 20, .6); backdrop-filter: blur(4px); }
        .modal-close { position: absolute; right: 1rem; top: .65rem; background: transparent; border: 0; font-size: 1.8rem; cursor: pointer; color: #6c6c78; }
        .modal-icon { color: #E50914; font-size: 2rem; margin-bottom: 1rem; }
        .landing-modal h2 { font-family: 'Space Grotesk', sans-serif; margin-bottom: .8rem; font-size: 1.7rem; }
        .landing-modal p { color: #6c6c78; margin-bottom: 1rem; }
        .modal-actions { display: flex; gap: .75rem; flex-wrap: wrap; margin-top: 1.5rem; }
        .modal-actions a { flex: 1; padding: .8rem 1rem; }
        .draft-label { display: inline-block; background: #f5f5f7; color: #6c6c78; border-radius: 6px; padding: .2rem .6rem; font-size: .75rem; margin-bottom: 1rem; }

</style>
<dialog id="info-modal" class="landing-modal" aria-labelledby="info-title">
    <button type="button" class="modal-close" data-close aria-label="Close">&times;</button>
    <span class="draft-label">Draft information</span>
    <h2 id="info-title"></h2>
    <div id="info-content"></div>
</dialog>
<script>
(() => {
        // Editable placeholder copy for footer information dialogs.
        const information = {
            help: ['Help Center', 'After logging in, browse a category, choose a shoe, and check the available sizes before adding it to your cart.', 'For order questions, keep your order number ready. Store contact details and support hours will be added here.'],
            size: ['Size Guide', 'Measure each foot from heel to longest toe while standing and use the larger measurement.', 'Sizing varies by brand and model. Compare your measurement with the brand size chart before ordering. A detailed chart will be added here.'],
            authenticity: ['Authenticity Check', 'Review the product details, labels, stitching, and packaging. Keep your receipt and original packaging for follow-up questions.', 'Prepare your order number and clear photos when asking about a product. Our verification process and contact details will be added here.'],
            tracking: ['Track Order', 'Log in to the account used for your purchase and open My Orders to review your order status.', 'Courier tracking guidance will be added here. This popup does not look up an order.'],
            about: ['About Achilles', 'Achilles is a family-owned footwear store built around a love of shoes and helping customers find their next pair.', 'Our store story, location, and opening hours will be added here.'],
            sustainability: ['Sustainability', 'Help your shoes last longer: clean them according to their material, air-dry them, and store them in a cool, dry place.', 'Details about store packaging and any sustainability initiatives will be added here.'],
            press: ['Press', 'This space will feature Achilles news, store announcements, and media resources.', 'A media contact and approved brand assets will be added here for press and collaboration inquiries.'],
            facebook: ['Find us on Facebook', 'Our official Facebook page link will be added here. Follow Achilles for store news and footwear updates.'],
            'x-twitter': ['Find us on X', 'Our official X profile link will be added here.'],
            tiktok: ['Find us on TikTok', 'Our official TikTok profile link will be added here. Follow Achilles for shoe videos and store updates.'],
        };

const infoModal = document.getElementById('info-modal');
function openModal(modal) { modal.showModal(); document.body.classList.add('modal-open'); }
        document.querySelectorAll('[data-info]').forEach(trigger => {
            trigger.setAttribute('aria-haspopup', 'dialog');
            trigger.addEventListener('click', event => {
                event.preventDefault();
                const [title, ...paragraphs] = information[trigger.dataset.info];
                document.getElementById('info-title').textContent = title;
                document.getElementById('info-content').replaceChildren(...paragraphs.map(text => {
                    const paragraph = document.createElement('p');
                    paragraph.textContent = text;
                    return paragraph;
                }));
                openModal(infoModal);
            });
        });
        document.querySelectorAll('.landing-modal').forEach(modal => {
            modal.querySelector('[data-close]').addEventListener('click', () => modal.close());
            modal.addEventListener('click', event => {
                const bounds = modal.getBoundingClientRect();
                if (event.target === modal && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) modal.close();
            });
            modal.addEventListener('close', () => document.body.classList.remove('modal-open'));
        });

})();
</script>
