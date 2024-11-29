<!DOCTYPE html>
<html>
<head>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .title-text {
            text-align: center;
            margin-bottom: 40px;
            color: #333;
        }
        
        .title-text h1 {
            font-size: 32px;
            margin: 0;
        }
        
        .centered-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        form {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .text-box {
            width: 100%;
            height: 200px;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
            font-size: 16px;
            line-height: 1.5;
        }
        
        .text-box:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 5px rgba(74, 144, 226, 0.3);
        }
        
        .submit-button {
            width: 200px;
            height: 50px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.2s ease;
        }
        
        .submit-button:hover {
            background-color: #357abd;
        }
        
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            width: 100%;
        }
        
        .success {
            background-color: #e7f3e8;
            color: #2d862f;
        }
        
        .error {
            background-color: #fde7e7;
            color: #c62828;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title-text">
            <h1>Bias Detection AI</h1>
        </div>
        <div class="centered-box">
            <form method="POST" action="">
                <textarea 
                    class="text-box" 
                    name="user_input" 
                    placeholder="Enter text here to analyze for potential bias..."
                ></textarea>
                <button type="submit" class="submit-button">Detect Bias</button>
            </form>
            
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $userInput = $_POST['user_input'];
                
                $apiUrl = 'http://127.0.0.1:5000/predict';
                $data = json_encode(['text' => $userInput]);
                
                $ch = curl_init($apiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data)
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                
                $response = curl_exec($ch);
                curl_close($ch);
                
                if ($response) {
                    $responseData = json_decode($response, true);
                
                    if (isset($responseData['prediction'])) {
                        // Extract the prediction value
                        $predictionValue = is_array($responseData['prediction']) ? reset($responseData['prediction']) : $responseData['prediction'];

                        // Ensure the prediction value is a scalar and numeric
                        if (is_scalar($predictionValue)) {
                            $predictionValue = (float) $predictionValue; // Explicitly cast to float
                        } else {
                            $predictionValue = 0.0; // Fallback for non-scalar data
                        }

                        // Threshold logic
                        $threshold = 0.6;
                        $isBiased = $predictionValue > $threshold;
                        if ($isBiased) {
                            $resultClass = 'error';
                            $resultText = 'Biased';
                        } else {
                            $resultClass = 'success';
                            $resultText = 'Not Biased';
                        }
                
                        // Display bias result
                        echo '<div class="result ' . $resultClass . '">
                                <p>' . $resultText . '</p>
                            </div>';
                

                        // BELOW: For testing purposes
                        // Display prediction value
                        // echo '<div class="result">
                        //         <p>Prediction: ' . htmlspecialchars($predictionValue) . '</p>
                        //     </div>';

                        // // Display isBiased value
                        // echo '<div class="result">
                        //         <p>isBiased: ' . ($isBiased ? 'true' : 'false') . '</p>
                        //     </div>';
                    } else {
                        echo '<div class="result error">
                                <p>Error: Unable to get prediction.</p>
                            </div>';
                    }
                } else {
                    echo '<div class="result error">
                            <p>Error: Could not connect to the API.</p>
                        </div>';
                }
                
            }
            ?>
        </div>
    </div>
</body>
</html>