/* ========================================================================
   CUSTOMER / VELORA INTERACTIONS
   ======================================================================== */

document.addEventListener("DOMContentLoaded", () => {
    /* ---------------------------------------------------------------------
       Spotlight
    --------------------------------------------------------------------- */
    document.querySelectorAll("[data-spotlight-card]").forEach((card) => {
        const update = (event) => {
            const rect = card.getBoundingClientRect();
            card.style.setProperty("--spot-x", `${event.clientX - rect.left}px`);
            card.style.setProperty("--spot-y", `${event.clientY - rect.top}px`);
            card.style.setProperty("--spot-radius", `${card.dataset.spotlightRadius || 320}px`);
            card.style.setProperty(
                "--spot-color",
                card.dataset.spotlightColor ||
                "color-mix(in oklab, var(--brand-from) 14%, transparent)"
            );
        };
        card.addEventListener("pointermove", update);
    });

    /* ---------------------------------------------------------------------
       Blur fade
    --------------------------------------------------------------------- */
    document.querySelectorAll("[data-blur-fade]").forEach((element) => {
        const once = element.dataset.blurFadeOnce !== "false";
        const delay = Number(element.dataset.blurFadeDelay || 0);
        const duration = Number(element.dataset.blurFadeDuration || 0.5);

        const show = () => {
            element.style.transition = [
                `opacity ${duration}s cubic-bezier(0.21, 0.47, 0.32, 0.98)`,
                `filter ${duration}s cubic-bezier(0.21, 0.47, 0.32, 0.98)`,
                `transform ${duration}s cubic-bezier(0.21, 0.47, 0.32, 0.98)`,
            ].join(",");
            element.style.transitionDelay = `${delay}s`;
            element.style.opacity = "1";
            element.style.filter = "blur(0)";
            element.style.transform = "translate3d(0,0,0)";
        };

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    show();
                    if (once) observer.unobserve(element);
                });
            },
            { rootMargin: "0px 0px -10% 0px", threshold: 0 }
        );

        observer.observe(element);
    });

    /* ---------------------------------------------------------------------
       Text reveal
    --------------------------------------------------------------------- */
    document.querySelectorAll("[data-text-reveal]").forEach((element) => {
        const words = element.querySelectorAll("[data-text-reveal-word]");
        const delay = Number(element.dataset.textRevealDelay || 0);
        const stagger = Number(element.dataset.textRevealStagger || 0.045);
        const once = element.dataset.textRevealOnce !== "false";
        let revealed = false;

        const reveal = () => {
            if (revealed && once) return;
            revealed = true;

            words.forEach((word, index) => {
                word.style.transition = [
                    "opacity .45s cubic-bezier(.21,.47,.32,.98)",
                    "transform .45s cubic-bezier(.21,.47,.32,.98)",
                    "filter .45s cubic-bezier(.21,.47,.32,.98)",
                ].join(",");
                word.style.transitionDelay = `${delay + index * stagger}s`;
                word.style.opacity = "1";
                word.style.transform = "translateY(0)";
                word.style.filter = "blur(0)";
            });
        };

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    reveal();
                    if (once) observer.unobserve(element);
                });
            },
            { rootMargin: "0px 0px -10% 0px", threshold: 0 }
        );
        observer.observe(element);
    });

    /* ---------------------------------------------------------------------
       Typewriter
    --------------------------------------------------------------------- */
    document.querySelectorAll("[data-typewriter]").forEach((element) => {
        let words = [];
        try {
            words = JSON.parse(element.dataset.typewriterWords || "[]");
        } catch (error) {
            console.error("Typewriter data is invalid.", error);
            return;
        }
        if (!Array.isArray(words) || words.length === 0) return;

        const textElement = element.querySelector("[data-typewriter-text]");
        const cursorElement = element.querySelector("[data-typewriter-cursor]");
        if (!textElement) return;

        const typeSpeed = Number(element.dataset.typewriterTypeSpeed || 70);
        const deleteSpeed = Number(element.dataset.typewriterDeleteSpeed || 40);
        const holdTime = Number(element.dataset.typewriterHoldTime || 1800);
        const loop = element.dataset.typewriterLoop !== "false";
        const cursor = element.dataset.typewriterCursor !== "false";
        const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

        if (reduced) {
            textElement.textContent = words[0];
            if (cursorElement) textElement.appendChild(cursorElement);
            return;
        }

        let wordIndex = 0;
        let text = "";
        let deleting = false;
        let timer = null;

        const render = () => {
            textElement.textContent = text;
            if (cursor && cursorElement) textElement.appendChild(cursorElement);
        };

        const tick = () => {
            const word = words[wordIndex % words.length];
            if (!deleting && text.length < word.length) {
                text = word.slice(0, text.length + 1);
                render();
                timer = setTimeout(tick, typeSpeed);
                return;
            }
            if (!deleting && text.length === word.length) {
                if (!loop && wordIndex === words.length - 1) return;
                timer = setTimeout(() => { deleting = true; tick(); }, holdTime);
                return;
            }
            if (deleting && text.length > 0) {
                text = word.slice(0, text.length - 1);
                render();
                timer = setTimeout(tick, deleteSpeed);
                return;
            }
            deleting = false;
            wordIndex = (wordIndex + 1) % words.length;
            timer = setTimeout(tick, deleteSpeed);
        };

        if (cursorElement) cursorElement.style.display = cursor ? "inline" : "none";
        render();
        tick();
        element.addEventListener("remove", () => clearTimeout(timer));
    });

    /* ---------------------------------------------------------------------
       Number ticker
    --------------------------------------------------------------------- */
    document.querySelectorAll("[data-number-ticker]").forEach((element) => {
        const value = Number(element.dataset.numberTickerValue || 0);
        const startValue = Number(element.dataset.numberTickerStart || 0);
        const delay = Number(element.dataset.numberTickerDelay || 0);
        const decimals = Number(element.dataset.numberTickerDecimals || 0);
        const prefix = element.dataset.numberTickerPrefix || "";
        const suffix = element.dataset.numberTickerSuffix || "";
        const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        const formatter = new Intl.NumberFormat("fa-IR", {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
        const format = (number) => `${prefix}${formatter.format(number)}${suffix}`;
        let started = false;

        const animate = () => {
            if (started) return;
            started = true;
            if (reduced) {
                element.textContent = format(value);
                return;
            }
            setTimeout(() => {
                const start = performance.now();
                const duration = 1200;
                const frame = (now) => {
                    const progress = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    element.textContent = format(startValue + (value - startValue) * eased);
                    if (progress < 1) requestAnimationFrame(frame);
                };
                requestAnimationFrame(frame);
            }, delay * 1000);
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                animate();
                observer.unobserve(element);
            });
        }, { rootMargin: "0px 0px -10% 0px", threshold: 0 });
        observer.observe(element);
    });

    /* ---------------------------------------------------------------------
       Tilt
    --------------------------------------------------------------------- */
    document.querySelectorAll("[data-tilt-card]").forEach((card) => {
        if (!window.matchMedia("(hover: hover) and (pointer: fine)").matches) return;
        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

        const maxTilt = Number(card.dataset.tiltMax || 8);
        let frame = null;
        let targetX = 0, targetY = 0;
        let currentX = 0, currentY = 0;

        const animate = () => {
            currentX += (targetX - currentX) * .12;
            currentY += (targetY - currentY) * .12;
            card.style.transform = `perspective(800px) rotateX(${currentX}deg) rotateY(${currentY}deg)`;
            if (Math.abs(targetX-currentX) > .01 || Math.abs(targetY-currentY) > .01) frame = requestAnimationFrame(animate);
            else frame = null;
        };
        const request = () => { if (!frame) frame = requestAnimationFrame(animate); };

        card.addEventListener("pointermove", (event) => {
            const rect = card.getBoundingClientRect();
            const px = (event.clientX - rect.left) / rect.width - .5;
            const py = (event.clientY - rect.top) / rect.height - .5;
            targetY = px * maxTilt * 2;
            targetX = -py * maxTilt * 2;
            request();
        });
        card.addEventListener("pointerleave", () => { targetX = 0; targetY = 0; request(); });
        card.addEventListener("pointercancel", () => { targetX = 0; targetY = 0; request(); });
    });
});
