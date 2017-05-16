<?php 
$phpFile = basename(filter_input(INPUT_SERVER,'PHP_SELF')); 
$authorizedPages = ['insertSiteUser.php','updateGuild.php','updateToons.php','updateWhatToon.php','insertToon.php','validUser.php','changePass.php'];
$searchPages = ['guildSummary.php','guildRoster.php'];
?>
 <style>
        body {
            background: url(wowbackground.jpg);
            background-size: cover;
        }
        
        
        </style>

<nav class="navbar sticky-top navbar-toggleable-lg navbar-inverse bg-darkgreen">
	<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<a class="navbar-brand" href="index.php">Esoteric</a>

	<div class="collapse navbar-collapse" id="navbarNavDropdown">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Raid Team Summary</a>
				<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
				  <a class="dropdown-item" href="guildSummary.php?timeFrame=ThisWeek">This Week</a>
				  <a class="dropdown-item" href="guildSummary.php?timeFrame=LastWeek">Last Week</a>
				  <a class="dropdown-item" href="guildSummary.php?timeFrame=ThisMonth">This Month</a>
                                  <a class="dropdown-item" href="guildSummary.php?timeFrame=LastMonth">Last Month</a>
				  <a class="dropdown-item" href="guildSummary.php?timeFrame=AllTime">All Time</a>
				</div>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="guildRoster.php">Raid Team Roster</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="singleCharacterView.php">Single Character View</a>
			</li>
		</ul>
		<?php 
		if (in_array($phpFile,$authorizedPages)) { ?>
		<ul class="navbar-nav ml-auto">
			<li class="nav-item<?php if ($phpFile === 'insertSiteUser.php') echo ' active'; ?>">
				<a class="nav-link" href="insertSiteUser.php">Add Authorized User</a>
			</li>
			<li class="nav-item<?php if ($phpFile === 'updateGuild.php' || $phpFile === 'updateToons.php' || $phpFile === 'updateWhatToon.php') echo ' active'; ?>">
				<a class="nav-link" href="updateWhatToon.php">Update Database Info</a>
			</li>
			<li class="nav-item<?php if ($phpFile === 'insertToon.php') echo ' active'; ?>">
				<a class="nav-link" href="insertToon.php">Insert/Update Toon</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="logout.php">Logout</a>
			</li>
		</ul>
		<?php 
		} else if (in_array($phpFile,$searchPages)) { ?>
			<form class="form-inline my-2 my-md-0" name="searchForm">
				<div class="btn-group" data-toggle="buttons">
					<label class="btn btn-outline-success <?php if ($queryMains) echo('active'); ?> ">
						<input type="checkbox" name="RaidTeam" value="Mains" <?php if ($queryMains) echo 'checked'; ?> autocomplete="off">Mains</label>
					<label class="btn btn-outline-success <?php if ($queryAlts) echo('active'); ?> ">
						<input type="checkbox" name="RaidTeam" value="Alts" <?php if ($queryAlts) echo 'checked'; ?> autocomplete="off">Alts</label>
					<label class="btn btn-outline-success <?php if ($queryAFK) echo('active'); ?> ">
						<input type="checkbox" name="RaidTeam" value="Afk" <?php if ($queryAFK) echo 'checked'; ?> autocomplete="off">AFK Raiders</label>
				</div>
                                <?php if ($phpFile === 'guildSummary.php') { ?>
				<div class="form-group mx-sm-3" style="display: none;">
					<input class="form-control" id="timeFrame" name="timeFrame" value="<?php if ($timeFrame) echo $timeFrame; ?>">
				</div>
                                <?php 
                                } ?>
				<div id="submit" style="margin-left:10px">
					<input type="submit" id="btnSubmit" class="btn btn-inverse btn-outline-success" value="Submit" />
				</div>
			</form>
		<?php 
		} else if ($phpFile === 'singleCharacterView.php') { ?>
			<form class="form-inline my-2 my-sm-0" name="searchForm">
				<select class="form-control btn-outline-success" id="toonName" name="toonName" onchange="configureDropDownLists(this,document.getElementById('toonSpec'))">
                                    <option>Toon Name</option>
                                    <?php 
                                    for ($x = 0; $x < sizeOf($toons); $x++) { ?>
                                        <option<?php if ($toons[$x]['ToonName'] === $toonName) echo ' selected'; ?>>
                                        <?php echo $toons[$x]['ToonName']; ?>
                                        </option>
                                    <?php
                                    } ?>
				</select>
				<select class="form-control btn-outline-success" id="toonSpec" name="toonSpec">
                                    <option>Spec Name</option>
                                    <?php 
                                    for ($x = 0; $x < sizeOf($toons); $x++) { 
                                        if ($toons[$x]['ToonName'] === $toonName) { 
                                            $specs = explode(',',$toons[$x]['Specs']);
                                            for ($y = 0; $y < sizeOf($specs); $y++) { ?>
                                                <option<?php if ($specs[$y] === $toonSpec) echo ' selected'; ?>>
                                                <?php echo $specs[$y];?>
                                                </option>
                                            <?php
                                            }
                                        }
                                    }
                                    ?>
				</select>
				<div id="submit" style="margin-left:10px">
                                    <input type="submit" id="btnSubmit" class="btn btn-inverse btn-outline-success" value="Submit" />
				</div>
			</form>
		<?php 
		} else if ($phpFile === 'index.php') {?>
			<form class="form-inline" action="" method="POST" role="form">
				<div class="form-group">
					<input type="text" class="form-control btn-outline-success" id="username" name="username" placeholder="Username">
				</div>
				<div class="form-group">
					<input type="password" class="form-control btn-outline-success" id="password" name="password" placeholder="Password">
				</div>
				<div id="submit" style="margin-left:10px">
					<button type="submit" name="submit" class="btn btn-inverse btn-outline-success">Login</button>
				</div>
			</form>
		<?php 
		} ?>
	</div>
</nav>