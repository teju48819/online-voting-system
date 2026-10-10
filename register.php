<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Define districts and constituencies
$districts = [
    'Bangalore Urban', 
    'Bangalore Rural', 
    'Mysore', 
    'Hubli-Dharwad', 
    'Belgaum', 
    'Gulbarga', 
    'Davanagere', 
    'Shimoga', 
    'Tumkur', 
    'Others'
];

$constituencies = [
    'Bangalore Urban' => [
        'Bangalore South', 
        'Bangalore North', 
        'Bangalore Central', 
        'Bangalore Rural'
    ],
    'Bangalore Rural' => [
        'Anekal', 
        'Devanahalli', 
        'Doddaballapur', 
        'Hoskote'
    ],
    'Mysore' => [
        'Mysore City', 
        'Chamundeshwari', 
        'Krishnaraja', 
        'Narasimharaja'
    ],
    'Hubli-Dharwad' => [
        'Hubli', 
        'Dharwad', 
        'Hubli Rural'
    ],
    'Belgaum' => [
        'Belgaum North', 
        'Belgaum South', 
        'Belgaum Rural'
    ],
    'Others' => ['General']
];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $voter_id = $_POST['voter_id'];
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $constituency = $_POST['constituency'];
    $district = $_POST['district'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if voter ID already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE voter_id = ? OR email = ?");
    $stmt->execute([$voter_id, $email]);
    
    if($stmt->rowCount() > 0) {
        $error = "Voter ID or Email already registered";
    } else {
        // Insert new voter
        $stmt = $conn->prepare("INSERT INTO users (voter_id, name, dob, gender, address, constituency, district, phone, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if($stmt->execute([$voter_id, $name, $dob, $gender, $address, $constituency, $district, $phone, $email, $password])) {
            header("Location: login.php?registered=1");
            exit();
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Registration - Karnataka Online Voting</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Additional styles for better form appearance */
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            flex: 1;
        }
        select, input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .btn {
            background-color: #0047AB;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <img src="assets/images/logo.png" alt="Karnataka Election Commission" class="logo">
            <h1>Voter Registration</h1>
        </div>
    </header>

    <main class="container">
        <section class="registration-form">
            <h2>Register as a Voter</h2>
            <?php if(isset($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="register.php" method="post">
                <div class="form-group">
                    <label for="voter_id">Voter ID Number</label>
                    <input type="text" id="voter_id" name="voter_id" required
                           value="<?php echo isset($_POST['voter_id']) ? htmlspecialchars($_POST['voter_id']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" required
                               value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select</option>
                            <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="district">District</label>
                        <select id="district" name="district" required>
                            <option value="">Select District</option>
                            <?php foreach($districts as $district): ?>
                                <option value="<?php echo $district; ?>"
                                    <?php echo (isset($_POST['district']) && $_POST['district'] == $district) ? 'selected' : ''; ?>>
                                    <?php echo $district; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="constituency">Constituency</label>
                        <select id="constituency" name="constituency" required>
                            <option value="">Select Constituency</option>
                            <?php 
                            // Pre-populate if district was already selected
                            if (isset($_POST['district'])) {
                                $selectedDistrict = $_POST['district'];
                                if (isset($constituencies[$selectedDistrict])) {
                                    foreach($constituencies[$selectedDistrict] as $constituency) {
                                        $selected = (isset($_POST['constituency']) && $_POST['constituency'] == $constituency) ? 'selected' : '';
                                        echo "<option value=\"$constituency\" $selected>$constituency</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">Register</button>
                </div>
            </form>
            
            <p>Already registered? <a href="login.php">Login here</a></p>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 Karnataka Election Commission. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const districtSelect = document.getElementById('district');
            const constituencySelect = document.getElementById('constituency');
            
            // Define constituencies (same as PHP)
            const constituencies = <?php echo json_encode($constituencies); ?>;
            
            // District change event handler
            districtSelect.addEventListener('change', function() {
                const selectedDistrict = this.value;
                
                // Clear existing options
                constituencySelect.innerHTML = '<option value="">Select Constituency</option>';
                
                // Add new options if district is selected
                if (selectedDistrict && constituencies[selectedDistrict]) {
                    constituencies[selectedDistrict].forEach(function(constituency) {
                        const option = document.createElement('option');
                        option.value = constituency;
                        option.textContent = constituency;
                        constituencySelect.appendChild(option);
                    });
                }
                
                // If constituency was previously selected, reselect it
                <?php if (isset($_POST['constituency']) && isset($_POST['district'])): ?>
                    if (selectedDistrict === '<?php echo $_POST["district"]; ?>') {
                        setTimeout(function() {
                            constituencySelect.value = '<?php echo $_POST["constituency"]; ?>';
                        }, 0);
                    }
                <?php endif; ?>
            });
            
            // Trigger change event if district is already selected (after form validation error)
            if (districtSelect.value) {
                districtSelect.dispatchEvent(new Event('change'));
            }
            
            // Password confirmation validation
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            function validatePassword() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords don't match");
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            password.onchange = validatePassword;
            confirmPassword.onkeyup = validatePassword;
        });
    </script>
</body>
</html>