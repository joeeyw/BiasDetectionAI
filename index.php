
<!DOCTYPE html>
<html>
<head>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden; /* Prevent scrolling */
            font-family: Arial, sans-serif;
        }
        .centered-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            
            height: 100vh;
        }
        .text-box {
            width: 50%;
            height: 300px;
            margin-bottom: 20px; /* Add some spacing below the text box */
        }
        .title-text {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .submit-button {
            width: 150px;
            height: 50px;
            background-color: blue;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .submit-button:hover {
            background-color: darkblue;
        }
    </style>
</head>
<body>
    <div class="title-text">
        <h1>Bias Detection AI</h1>
    </div>
    <div class="centered-box">
        <textarea class="text-box" placeholder="Enter text here..."></textarea>
        <button class="submit-button">Detect Bias</button>
    </div>
    
    <!-- <script>
        // Add an event listener to the button
        document.querySelector('.submit-button').addEventListener('click', function() {
            // Get the text from the textarea
            const text = document.querySelector('.text-box').value;
            // Send the text to the server
            fetch('http://localhost:5000/detect-bias', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ text: text })
            })
            .then(response => response.json())
            .then(data => {
                // Display the result
                alert(data.result);
            });
        });
    </script> -->
</body>
</html>