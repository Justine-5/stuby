const menu = document.querySelectorAll(".menu");
const sidebarLabels = document.querySelectorAll(".aside-labels");
const sidebar = document.querySelector(".sidebar");
const overlay = document.querySelector(".overlay")


function mobile() {
    sidebar.classList.toggle("visible");
    overlay.classList.toggle("mobile-overlay");
    document.body.classList.toggle("no-scroll");

    sidebarLabels.forEach(element => {
        element.classList.toggle("aside-labels-hidden")
    });
}

menu[0].addEventListener("click", mobile);
menu[1].addEventListener("click", mobile);
overlay.addEventListener("click", mobile);

const profileButton = document.querySelector('.profile');
const profileInfo = document.querySelector('.profile-info');

profileButton.addEventListener('focus', () => {
    profileInfo.style.display = 'flex';
});

document.addEventListener("click", (e) => {
    if (!profileButton.contains(e.target) && !profileInfo.contains(e.target)) {
        profileInfo.style.display = "none";
    }
});

// search
document.addEventListener('DOMContentLoaded', () => {
    const desktopSearchInput = document.querySelector('.search.search-desktop input[type="search"]');
    const desktopSearchResult = document.querySelector('.search.search-desktop .search-result');

    const mobileSearchInput = document.querySelector('.search.search-mobile input[type="search"]');
    let mobileSearchResult = document.querySelector('.search.search-mobile .search-result');

    const handleSearch = (input, resultContainer) => {
        const query = input.value.trim();

        if (query.length === 0) {
            resultContainer.classList.add('hidden');
            resultContainer.innerHTML = '';
            return;
        }

        fetch(`search.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                resultContainer.classList.remove('hidden');
                resultContainer.innerHTML = '';

                if (data.length > 0) {
                    data.forEach(deck => {
                        const resultLink = document.createElement('a');
                        resultLink.href = `deck-info.php?id=${deck.id}`;
                        resultLink.innerHTML = `<p class="result-text">${deck.name}</p>`;
                        resultLink.addEventListener('click', () => {
                            input.value = ''; // Clear input
                            resultContainer.classList.add('hidden'); // Hide result container
                        });
                        resultContainer.appendChild(resultLink);
                    });
                } else {
                    resultContainer.innerHTML = '<p>No decks found.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching search results:', error);
            });
    };

    const handleFocusAndClick = (input, resultContainer) => {
        document.addEventListener('mousedown', (event) => {
            if (!input.contains(event.target) && !resultContainer.contains(event.target)) {
                resultContainer.classList.add('hidden');
            }
        });

        input.addEventListener('focus', () => {
            if (input.value.trim().length > 0) {
                resultContainer.classList.remove('hidden');
            }
        });
    };

    if (desktopSearchInput && desktopSearchResult) {
        desktopSearchInput.addEventListener('input', () => handleSearch(desktopSearchInput, desktopSearchResult));
        handleFocusAndClick(desktopSearchInput, desktopSearchResult);
    }

    if (mobileSearchInput) {
        if (!mobileSearchResult) {
            mobileSearchResult = document.createElement('div');
            mobileSearchResult.classList.add('search-result', 'hidden');
            mobileSearchInput.parentNode.appendChild(mobileSearchResult);
        }

        mobileSearchInput.addEventListener('input', () => handleSearch(mobileSearchInput, mobileSearchResult));
        handleFocusAndClick(mobileSearchInput, mobileSearchResult);
    }
});



