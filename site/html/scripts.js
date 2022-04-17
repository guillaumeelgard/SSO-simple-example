const post = async (url, data, callback) => {

    let response
    try {
        const r = await fetch(url, {
            method: 'POST',
            body: JSON.stringify(data),
        });
        response = await r.json()
        if( ! r.ok)
        {
            console.error(r, data)
            return
        }
    }
    catch (e) {
        console.error(e)
    }

    callback(response)
}