<?php

/**
 * Template to display [exms_student_dashboard] shortcode chat content
 */
if (! defined('ABSPATH')) exit;
?>
<div class="exms-std-chat-content">
    
    <div class="exms-std-chat-filter">
        <h4> <?php echo __('Groups') ?></h4>
        <div class="exms-std-chat-filter-fields">
            <select name="" id="">
                <option value="today"><?php echo __('25Exam Skill Myrtle 1', 'exms'); ?></option>
                <option value="yesterday"><?php echo __('25Exam Skill Myrtle 2', 'exms'); ?></option>
                <option value="this week"><?php echo __('25Exam Skill Myrtle 3', 'exms'); ?></option>
            </select>
        </div>
    </div>
    <div class="exms-std-chat-wrap">
        <div class="exms-chat-shell">
  <div class="exms-chat-card">

    <!-- LEFT -->
    <aside class="exms-chat-left">
      <div class="exms-chat-left-head">
        <div class="exms-chat-h1">Messages</div>

        <div class="exms-chat-search">
          <span class="dashicons dashicons-search"></span>
          <input type="text" placeholder="search Message or Name" />
        </div>
      </div>

      <div class="exms-chat-users">
        <!-- user row -->
        <div class="exms-user-row is-active">
          <img class="exms-user-avatar" src="https://i.pravatar.cc/80?img=32" alt="">
          <div class="exms-user-meta">
            <div class="exms-user-name">Lavern Laboy</div>
            <div class="exms-user-last">Haha that's terrifying 😂</div>
          </div>
          <div class="exms-user-time">1h</div>
        </div>

        <!-- repeat rows -->
        <div class="exms-user-row">
          <img class="exms-user-avatar" src="https://i.pravatar.cc/80?img=32" alt="">
          <div class="exms-user-meta">
            <div class="exms-user-name">Lavern Laboy</div>
            <div class="exms-user-last">Haha that's terrifying 😂</div>
          </div>
          <div class="exms-user-time">1h</div>
        </div>

        <div class="exms-user-row">
          <img class="exms-user-avatar" src="https://i.pravatar.cc/80?img=15" alt="">
          <div class="exms-user-meta">
            <div class="exms-user-name">Lavern Laboy</div>
            <div class="exms-user-last">Haha that's terrifying 😂</div>
          </div>
          <div class="exms-user-time">1h</div>
        </div>

        <div class="exms-user-row">
          <img class="exms-user-avatar" src="https://i.pravatar.cc/80?img=12" alt="">
          <div class="exms-user-meta">
            <div class="exms-user-name">Lavern Laboy</div>
            <div class="exms-user-last">Haha that's terrifying 😂</div>
          </div>
          <div class="exms-user-time">1h</div>
        </div>
      </div>
    </aside>

    <!-- RIGHT -->
    <section class="exms-chat-right">

      <div class="exms-chat-panel">
        <!-- header -->
        <div class="exms-chat-top">
          <div class="exms-chat-top-left">
            <img class="exms-chat-top-avatar" src="https://i.pravatar.cc/80?img=32" alt="">
            <div>
              <div class="exms-chat-top-name">Lavern Laboy</div>
              <div class="exms-chat-top-sub">
                <span class="exms-online">Online</span>
                <span class="exms-dotsep">-</span>
                <span class="exms-lastseen">Last seen, 2.02pm</span>
              </div>
            </div>
          </div>

          <button class="exms-chat-more" type="button" aria-label="More">
            <span class="dashicons dashicons-ellipsis"></span>
          </button>
        </div>

        <!-- body -->
        <div class="exms-chat-body" id="exmsChatBody">
          <div class="exms-msg is-left">
            <img class="exms-msg-av" src="https://i.pravatar.cc/80?img=32" alt="">
            <div class="exms-bubble">omg, this is amazing</div>
          </div>

          <div class="exms-msg is-left">
            <img class="exms-msg-av" src="https://i.pravatar.cc/80?img=32" alt="">
            <div class="exms-bubble">perfect! ✅</div>
          </div>

          <div class="exms-msg is-left">
            <img class="exms-msg-av" src="https://i.pravatar.cc/80?img=32" alt="">
            <div class="exms-bubble">Wow, this is really epic</div>
          </div>

          <div class="exms-msg is-right">
            <div class="exms-bubble is-me">How are you?</div>
            <img class="exms-msg-av" src="https://i.pravatar.cc/80?img=12" alt="">
          </div>

          <div class="exms-msg is-left">
            <img class="exms-msg-av" src="https://i.pravatar.cc/80?img=32" alt="">
            <div class="exms-bubble">I'm good bro</div>
          </div>

          <div class="exms-msg is-left">
            <img class="exms-msg-av" src="https://i.pravatar.cc/80?img=32" alt="">
            <div class="exms-bubble">perfect! ✅</div>
          </div>

          <div class="exms-msg is-left">
            <img class="exms-msg-av" src="https://i.pravatar.cc/80?img=32" alt="">
            <div class="exms-bubble">just ideas for next time</div>
          </div>

          <div class="exms-msg is-right">
            <div class="exms-bubble is-me">wooooooo</div>
            <img class="exms-msg-av" src="https://i.pravatar.cc/80?img=12" alt="">
          </div>

          <div class="exms-msg is-right">
            <div class="exms-bubble is-me">Haha that's terrifying 😂</div>
            <img class="exms-msg-av" src="https://i.pravatar.cc/80?img=12" alt="">
          </div>

          <div class="exms-msg is-right">
            <div class="exms-bubble is-me">What are you doing now a days</div>
            <img class="exms-msg-av" src="https://i.pravatar.cc/80?img=12" alt="">
          </div>
        </div>

        <!-- composer -->
        <div class="exms-chat-compose">
          <button class="exms-compose-ic" type="button" aria-label="Attachment">
            <span class="dashicons dashicons-paperclip"></span>
          </button>
          <button class="exms-compose-ic" type="button" aria-label="Emoji">
            <span class="dashicons dashicons-smiley"></span>
          </button>

          <div class="exms-compose-input">
            <input id="exmsChatInput" type="text" placeholder="Type a message" />
            <button id="exmsChatSend" class="exms-send" type="button" aria-label="Send">
              <span class="dashicons dashicons-arrow-right-alt"></span>
            </button>
          </div>
        </div>

      </div>
    </section>

  </div>
</div>

    </div>

</div>