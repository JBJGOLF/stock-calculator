jQuery(document).ready(function($) {
    console.log('대출이자 계산기 초기화 시작');
    
    // 즉시 실행 이벤트 설정
    function setupEvents() {
        console.log('이벤트 설정 중...');
        
        // 상환방식 선택 처리
        $('input[name="loan-type"]').on('change', function() {
            console.log('상환방식 변경:', this.value);
            
            // 모든 라벨 초기화
            $('input[name="loan-type"]').each(function() {
                $(this).parent().css({
                    'background': 'linear-gradient(145deg,#e8dbc2,#f5f1e6)',
                    'color': '#8b6914'
                });
            });
            
            // 선택된 라벨 스타일 변경
            $(this).parent().css({
                'background': 'linear-gradient(145deg,#D4AF37,#B8860B)',
                'color': 'white'
            });
            
            // 거치기간 표시 여부
            if (this.value === 'equal-payment' || this.value === 'equal-principal') {
                $('#grace-period-row').show();
                console.log('거치기간 필드 표시됨');
            } else {
                $('#grace-period-row').hide();
                console.log('거치기간 필드 숨겨짐');
            }
        });
        
        // 대출금액 천단위 구분 기능
        $('#loan-amount').on('input', function() {
            this.value = this.value.replace(/[^\d]/g, '').replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
        });
        
        // 계산하기 버튼 클릭 처리
        $('#calc-btn').on('click', function(e) {
            e.preventDefault();
            console.log('계산하기 버튼 클릭됨');
            calculateLoan();
        });
        
        // 다시 계산하기 버튼 클릭 처리
        $('#reset-btn').on('click', function(e) {
            e.preventDefault();
            console.log('다시 계산하기 버튼 클릭됨');
            $('#result-content').hide();
            $('html, body').animate({
                scrollTop: $('.loan-calculator').offset().top - 50
            }, 500);
        });
        
        console.log('이벤트 설정 완료');
    }
    
    // 모바일 감지 함수
    function isMobile() {
        return window.innerWidth <= 768;
    }
    
    // 화면 크기에 따른 테이블/카드 표시
    function updateDisplayMode() {
        if (isMobile()) {
            $('.pc-table').hide();
            $('.mobile-cards').show();
        } else {
            $('.pc-table').show();
            $('.mobile-cards').hide();
        }
    }
    
    // 천단위 구분 함수
    function formatNumber(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    // 화면 크기 변경 시 테이블/카드 표시 업데이트
    $(window).on('resize', function() {
        if ($('#result-content').is(':visible')) {
            updateDisplayMode();
        }
    });
    
    // 대출 계산 함수
    function calculateLoan() {
        try {
            console.log('대출 계산 시작');
            
            // 선택된 대출 유형 찾기
            var loanType = $('input[name="loan-type"]:checked').val();
            console.log('선택된 대출 유형:', loanType);
            
            var loanTypeName = '';
            
            // 상환방식에 따른 이름 설정
            if (loanType === 'bullet') {
                loanTypeName = '만기일시상환';
            } else if (loanType === 'equal-payment') {
                loanTypeName = '원리금균등상환';
            } else if (loanType === 'equal-principal') {
                loanTypeName = '원금균등상환';
            }
            
            // 입력값 가져오기
            var loanAmountStr = $('#loan-amount').val().replace(/,/g, '');
            var loanAmount = parseFloat(loanAmountStr) * 10000; // 만원 단위 입력
            var interestRate = parseFloat($('#interest-rate').val()) / 100;
            var loanTerm = parseInt($('#loan-term').val());
            var gracePeriod = 0;
            
            // 거치기간 설정 (원리금균등상환, 원금균등상환인 경우에만)
            if (loanType === 'equal-payment' || loanType === 'equal-principal') {
                gracePeriod = parseInt($('#grace-period').val()) || 0;
                console.log('거치기간:', gracePeriod, '개월');
            }
            
            console.log('입력값:', {
                금액: loanAmount,
                이자율: interestRate,
                기간: loanTerm,
                거치기간: gracePeriod
            });
            
            // 입력값 검증
            if (isNaN(loanAmount) || isNaN(interestRate) || isNaN(loanTerm) || loanAmount <= 0 || loanTerm <= 0 || interestRate < 0) {
                alert('모든 필드를 유효한 값으로 입력해주세요.');
                return;
            }
            
            if ((loanType === 'equal-payment' || loanType === 'equal-principal') && gracePeriod >= loanTerm) {
                alert('거치기간은 대출기간보다 작아야 합니다.');
                return;
            }
            
            // 요약 정보 표시
            $('#res-loan-type').html(loanTypeName);
            $('#res-loan-amount').html(formatNumber(loanAmountStr) + '만원');
            $('#res-loan-rate').html((interestRate * 100).toFixed(1) + '%');
            
            // 계산 결과 변수
            var totalPrincipal = 0;
            var totalInterest = 0;
            var scheduleHTML = '';
            var mobileScheduleHTML = '';
            
            // 상환 방식에 따른 계산
            var monthlyRate = interestRate / 12;
            
            if (loanType === 'bullet') {
                // 만기일시상환 계산
                var monthlyInterest = loanAmount * monthlyRate;
                
                for (var i = 1; i <= loanTerm; i++) {
                    var principalPayment = (i === loanTerm) ? loanAmount : 0;
                    var interestPayment = monthlyInterest;
                    var payment = principalPayment + interestPayment;
                    var remainingBalance = (i === loanTerm) ? 0 : loanAmount;
                    
                    totalPrincipal += principalPayment;
                    totalInterest += interestPayment;
                    
                    // PC용 테이블 행 추가
                    var rowStyle = i % 2 === 0 ? 'background: #fff;' : 'background: #f9f6f0;';
                    scheduleHTML += '<tr style="' + rowStyle + '">';
                    scheduleHTML += '<td style="padding: 8px; text-align: center; border: 1px solid #d6c191;">' + i + '</td>';
                    scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(principalPayment)) + '원</td>';
                    scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(interestPayment)) + '원</td>';
                    scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(payment)) + '원</td>';
                    scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(remainingBalance)) + '원</td>';
                    scheduleHTML += '</tr>';
                    
                    // 모바일용 카드 추가
                    mobileScheduleHTML += '<div style="background: ' + (i % 2 === 0 ? '#fff' : '#f9f6f0') + '; border: 1px solid #d6c191; border-radius: 8px; margin-bottom: 8px; overflow: hidden;">';
                    mobileScheduleHTML += '<div style="background: linear-gradient(145deg,#D4AF37,#B8860B); color: white; text-align: center; padding: 8px; font-weight: bold;">' + i + '회차</div>';
                    mobileScheduleHTML += '<div style="padding: 10px;">';
                    mobileScheduleHTML += '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span>납입원금:</span><span style="font-weight: bold;">' + formatNumber(Math.round(principalPayment)) + '원</span></div>';
                    mobileScheduleHTML += '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span>이자:</span><span style="font-weight: bold;">' + formatNumber(Math.round(interestPayment)) + '원</span></div>';
                    mobileScheduleHTML += '<div style="display: flex; justify-content: space-between; margin-bottom: 5px; border-top: 1px dashed #d6c191; padding-top: 5px;"><span>납입금액:</span><span style="font-weight: bold;">' + formatNumber(Math.round(payment)) + '원</span></div>';
                    mobileScheduleHTML += '<div style="display: flex; justify-content: space-between;"><span>대출잔액:</span><span style="font-weight: bold;">' + formatNumber(Math.round(remainingBalance)) + '원</span></div>';
                    mobileScheduleHTML += '</div>';
                    mobileScheduleHTML += '</div>';
                }
            } else if (loanType === 'equal-payment') {
                // 원리금균등상환 계산
                var repaymentTerm = loanTerm - gracePeriod;
                var pmt = 0;
                
                if (monthlyRate === 0) {
                    // 이자율이 0인 경우
                    pmt = loanAmount / repaymentTerm;
                } else {
                    // PMT 공식: PMT = P * r * (1+r)^n / ((1+r)^n - 1)
                    pmt = loanAmount * monthlyRate * Math.pow(1 + monthlyRate, repaymentTerm) / (Math.pow(1 + monthlyRate, repaymentTerm) - 1);
                }
                
                var remainingBalance = loanAmount;
                
                for (var i = 1; i <= loanTerm; i++) {
                    var interestPayment = remainingBalance * monthlyRate;
                    var principalPayment = 0;
                    var payment = 0;
                    var isGracePeriod = i <= gracePeriod;
                    
                    if (isGracePeriod) {
                        // 거치기간 동안 이자만 납부
                        payment = interestPayment;
                        principalPayment = 0;
                    } else {
                        // 상환기간 동안 원리금균등상환
                        payment = pmt;
                        principalPayment = payment - interestPayment;
                    }
                    
                    remainingBalance -= principalPayment;
                    
                    // 마지막 달에 반올림 오차 보정
                    if (i === loanTerm) {
                        principalPayment += remainingBalance;
                        payment = principalPayment + interestPayment;
                        remainingBalance = 0;
                    }
                    
                    totalPrincipal += principalPayment;
                    totalInterest += interestPayment;
                    
                    // PC용 테이블 행 추가
                    var rowStyle = i % 2 === 0 ? 'background: #fff;' : 'background: #f9f6f0;';
                    if (isGracePeriod) rowStyle += '; background-color: #f0eadb;';
                    
                    scheduleHTML += '<tr style="' + rowStyle + '">';
                    scheduleHTML += '<td style="padding: 8px; text-align: center; border: 1px solid #d6c191;">' + i + (isGracePeriod ? ' <span style="display: inline-block; font-size: 11px; padding: 2px 5px; border-radius: 3px; background: #8b6914; color: white;">거치</span>' : '') + '</td>';
                    scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(principalPayment)) + '원</td>';
                    scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(interestPayment)) + '원</td>';
                    scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(payment)) + '원</td>';
                    scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(remainingBalance)) + '원</td>';
                    scheduleHTML += '</tr>';
                    
                    // 모바일용 카드 추가
                    var cardBg = i % 2 === 0 ? '#fff' : '#f9f6f0';
                    if (isGracePeriod) cardBg = '#f0eadb';
                    
                    mobileScheduleHTML += '<div style="background: ' + cardBg + '; border: 1px solid #d6c191; border-radius: 8px; margin-bottom: 8px; overflow: hidden;">';
                    mobileScheduleHTML += '<div style="background: linear-gradient(145deg,#D4AF37,#B8860B); color: white; text-align: center; padding: 8px; font-weight: bold;">' + i + '회차' + (isGracePeriod ? ' <span style="display: inline-block; font-size: 11px; padding: 2px 5px; border-radius: 3px; background: rgba(0,0,0,0.2); color: white;">거치기간</span>' : '') + '</div>';
                    mobileScheduleHTML += '<div style="padding: 10px;">';
                    mobileScheduleHTML += '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span>납입원금:</span><span style="font-weight: bold;">' + formatNumber(Math.round(principalPayment)) + '원</span></div>';
                    mobileScheduleHTML += '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span>이자:</span><span style="font-weight: bold;">' + formatNumber(Math.round(interestPayment)) + '원</span></div>';
                    mobileScheduleHTML += '<div style="display: flex; justify-content: space-between; margin-bottom: 5px; border-top: 1px dashed #d6c191; padding-top: 5px;"><span>납입금액:</span><span style="font-weight: bold;">' + formatNumber(Math.round(payment)) + '원</span></div>';
                    mobileScheduleHTML += '<div style="display: flex; justify-content: space-between;"><span>대출잔액:</span><span style="font-weight: bold;">' + formatNumber(Math.round(remainingBalance)) + '원</span></div>';
                    mobileScheduleHTML += '</div>';
                    mobileScheduleHTML += '</div>';
                }
            } else if (loanType === 'equal-principal') {
                // 원금균등상환 계산
                var repaymentTerm = loanTerm - gracePeriod;
                var monthlyPrincipal = loanAmount / repaymentTerm;
                var remainingBalance = loanAmount;
                
                for (var i = 1; i <= loanTerm; i++) {
                    var interestPayment = remainingBalance * monthlyRate;
                    var principalPayment = 0;
                    var isGracePeriod = i <= gracePeriod;
                    
                    if (isGracePeriod) {
                        // 거치기간 동안 이자만 납부
                        principalPayment = 0;
                    } else {
                        // 상환기간 동안 원금균등상환
                        principalPayment = monthlyPrincipal;
                    }
                    
                    var payment = principalPayment + interestPayment;
                    remainingBalance -= principalPayment;
                    
                    // 마지막 달에 반올림 오차 보정
                    if (i === loanTerm) {
                        principalPayment += remainingBalance;
                        payment = principalPayment + interestPayment;
                        remainingBalance = 0;
                    }
                    
                    totalPrincipal += principalPayment;
                    totalInterest += interestPayment;
                    
                    // PC용 테이블 행 추가
                    var rowStyle = i % 2 === 0 ? 'background: #fff;' : 'background: #f9f6f0;';
                    if (isGracePeriod) rowStyle += '; background-color: #f0eadb;';
                    
                    scheduleHTML += '<tr style="' + rowStyle + '">';
                    scheduleHTML += '<td style="padding: 8px; text-align: center; border: 1px solid #d6c191;">' + i + (isGracePeriod ? ' <span style="display: inline-block; font-size: 11px; padding: 2px 5px; border-radius: 3px; background: #8b6914; color: white;">거치</span>' : '') + '</td>';
                    scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(principalPayment)) + '원</td>';
                    scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(interestPayment)) + '원</td>';
                    scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(payment)) + '원</td>';
                    scheduleHTML += '<td style="padding: 8px; text-align: right; border: 1px solid #d6c191;">' + formatNumber(Math.round(remainingBalance)) + '원</td>';
                    scheduleHTML += '</tr>';
                    
                    // 모바일용 카드 추가
                    var cardBg = i % 2 === 0 ? '#fff' : '#f9f6f0';
                    if (isGracePeriod) cardBg = '#f0eadb';
                    
                    mobileScheduleHTML += '<div style="background: ' + cardBg + '; border: 1px solid #d6c191; border-radius: 8px; margin-bottom: 8px; overflow: hidden;">';
                    mobileScheduleHTML += '<div style="background: linear-gradient(145deg,#D4AF37,#B8860B); color: white; text-align: center; padding: 8px; font-weight: bold;">' + i + '회차' + (isGracePeriod ? ' <span style="display: inline-block; font-size: 11px; padding: 2px 5px; border-radius: 3px; background: rgba(0,0,0,0.2); color: white;">거치기간</span>' : '') + '</div>';
                    mobileScheduleHTML += '<div style="padding: 10px;">';
                    mobileScheduleHTML += '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span>납입원금:</span><span style="font-weight: bold;">' + formatNumber(Math.round(principalPayment)) + '원</span></div>';
                    mobileScheduleHTML += '<div style="display: flex; justify-content: space-between; margin-bottom: 5px;"><span>이자:</span><span style="font-weight: bold;">' + formatNumber(Math.round(interestPayment)) + '원</span></div>';
                    mobileScheduleHTML += '<div style="display: flex; justify-content: space-between; margin-bottom: 5px; border-top: 1px dashed #d6c191; padding-top: 5px;"><span>납입금액:</span><span style="font-weight: bold;">' + formatNumber(Math.round(payment)) + '원</span></div>';
                    mobileScheduleHTML += '<div style="display: flex; justify-content: space-between;"><span>대출잔액:</span><span style="font-weight: bold;">' + formatNumber(Math.round(remainingBalance)) + '원</span></div>';
                    mobileScheduleHTML += '</div>';
                    mobileScheduleHTML += '</div>';
                }
            }

            console.log('계산 완료');
            
            // PC용 결과 표시
            $('#payment-schedule').html(scheduleHTML);
            $('#res-total-principal').html(formatNumber(Math.round(totalPrincipal)) + '원');
            $('#res-total-interest').html(formatNumber(Math.round(totalInterest)) + '원');
            $('#res-total-payment').html(formatNumber(Math.round(totalPrincipal + totalInterest)) + '원');
            
            // 모바일용 결과 표시
            $('#mobile-payment-schedule').html(mobileScheduleHTML);
            $('#mobile-total-principal').html(formatNumber(Math.round(totalPrincipal)) + '원');
            $('#mobile-total-interest').html(formatNumber(Math.round(totalInterest)) + '원');
            $('#mobile-total-payment').html(formatNumber(Math.round(totalPrincipal + totalInterest)) + '원');
            
            // 결과 보이기
            $('#result-content').show();
            
            // 화면 크기에 따라 테이블/카드 표시 업데이트
            updateDisplayMode();
            
            // 결과로 스크롤
            $('html, body').animate({
                scrollTop: $('#loan-result').offset().top - 50
            }, 500);
            
        } catch (e) {
            console.error('계산 중 오류 발생:', e);
            alert('계산 중 오류가 발생했습니다: ' + e.message);
        }
    }
    
    // 이벤트 설정 호출
    setupEvents();
    
    // 페이지 로드 완료 후 1초 뒤에 추가 이벤트 바인딩 (안전장치)
    setTimeout(function() {
        console.log('추가 이벤트 바인딩 실행');
        
        // 계산하기 버튼에 직접 클릭 이벤트 재설정
        $('#calc-btn').off('click').on('click', function(e) {
            e.preventDefault();
            console.log('계산하기 버튼 클릭 처리 (지연 바인딩)');
            calculateLoan();
        });
        
        // 다시 계산하기 버튼에 직접 클릭 이벤트 재설정
        $('#reset-btn').off('click').on('click', function(e) {
            e.preventDefault();
            console.log('다시 계산하기 버튼 클릭 처리 (지연 바인딩)');
            $('#result-content').hide();
            $('html, body').animate({
                scrollTop: $('.loan-calculator').offset().top - 50
            }, 500);
        });
        
        // 상환방식 라디오 버튼 이벤트 재설정
        var currentType = $('input[name="loan-type"]:checked').val();
        console.log('현재 선택된 상환방식:', currentType);
        
        if (currentType === 'equal-payment' || currentType === 'equal-principal') {
            $('#grace-period-row').show();
            console.log('거치기간 필드 표시 (지연 처리)');
        }
    }, 1000);
});