export async function getRequest(url, params = {}) {
    // Build query string (handle empty object gracefully)
    const queryString = new URLSearchParams(params).toString();
    const fullUrl = queryString ? `${url}?${queryString}` : url;

    try {
        const response = await fetch(fullUrl);

        // Check for successful response
        if (!response.ok) {
            throw new Error(`GET request to ${url} failed with status ${response.status}`);
        }

        return response;
    } catch (error) {
        console.error('Error fetching data:', error);
        // throw error; // Re-throw for potential handling at the call site
    }
}

export async function postRequest(url, params = {}) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
                // 'Content-Type': 'application/x-www-form-urlencoded' // for $_POST
            },
            body: JSON.stringify(params) // Replace with your data to be sent
        });

        // Check for successful response
        if (!response.ok) {
            throw new Error(`POST request to ${url} failed with status ${response.status}`);
        }

        return response.json();
    } catch (error) {
        console.error('Error posting data:', error);
        // throw error; // Re-throw for potential handling at the call site
    }
}