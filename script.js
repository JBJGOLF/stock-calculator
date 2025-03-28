// 페이지가 로드되면 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 이벤트 리스너 등록
    initEventListeners();
    
    // 초기 설정: 증권사 선택에 따라 수수료 입력란 상태 업데이트
    updateCommission();
});

// 이벤트 리스너 초기화
function initEventListeners() {
    // 탭 전환 기능
    const tabs = document.querySelectorAll('#stockCalc .form-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // 모든 탭에서 active 클래스 제거
            tabs.forEach(t => t.classList.remove('active'));
            
            // 현재 클릭한 탭에 active 클래스 추가
            this.classList.add('active');
            
            // 모든 탭 컨텐츠 숨기기
            const tabContents = document.querySelectorAll('#stockCalc .tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // 선택한 탭의 컨텐츠 표시
            const targetTab = this.getAttribute('data-tab');
            document.getElementById(targetTab).classList.add('active');
        });
    });
    
    // 증권사 선택 변경 이벤트
    document.getElementById('brokerSelect').addEventListener('change', updateCommission);
    
    // 숫자 입력 필드에 포맷팅 함수 연결
    document.getElementById('avgBuyPrice').addEventListener('input', function() {
        formatNumber(this);
    });
    
    document.getElementById('stockQuantity').addEventListener('input', function() {
        formatNumber(this);
    });
    
    document.getElementById('currentPrice').addEventListener('input', function() {
        formatNumber(this);
    });
    
    // 계산하기 버튼 클릭 이벤트
    document.getElementById('calculateBtn').addEventListener('click', calculateProfit);
    
    // 다시 계산하기 버튼 클릭 이벤트
    document.getElementById('recalculateBtn').addEventListener('click', resetForm);
}

// 숫자 포맷팅 (천 단위 콤마 추가)
function formatNumber(input) {
    // 입력 값에서 콤마 제거
    let value = input.value.replace(/,/g, '');
    
    // 숫자만 허용
    value = value.replace(/[^0-9]/g, '');
    
    // 천 단위 콤마 추가
    if (value) {
        value = Number(value).toLocaleString('ko-KR');
    }
    
    // 포맷된 값으로 업데이트
    input.value = value;
}

// 문자열에서 숫자만 추출 (계산용)
function extractNumber(str) {
    return parseFloat(str.replace(/,/g, '')) || 0;
}

// 증권사 선택에 따른 수수료 입력란 상태 업데이트
function updateCommission() {
    const brokerSelect = document.getElementById('brokerSelect');
    const customCommissionGroup = document.getElementById('customCommissionGroup');
    const commissionInput = document.getElementById('commission');
    
    if (brokerSelect.value === 'direct') {
        // 직접 입력 선택 시 수수료 입력란 표시
        customCommissionGroup.style.display = 'block';
    } else {
        // 증권사 선택 시 수수료 입력란 숨기고 선택된 값으로 설정
        customCommissionGroup.style.display = 'none';
        commissionInput.value = brokerSelect.value;
    }
}

// 수익률 계산 함수
function calculateProfit() {
    // 입력값 가져오기
    const avgBuyPrice = extractNumber(document.getElementById('avgBuyPrice').value);
    const stockQuantity = extractNumber(document.getElementById('stockQuantity').value);
    const currentPrice = extractNumber(document.getElementById('currentPrice').value);
    const commissionRate = parseFloat(document.getElementById('commission').value || document.getElementById('brokerSelect').value) / 100;
    
    // 입력값 검증
    if (!avgBuyPrice || !stockQuantity || !currentPrice) {
        alert('모든 항목을 정확히 입력해주세요.');
        return;
    }
    
    // 매수 금액 계산
    const buyAmount = avgBuyPrice * stockQuantity;
    
    // 매도 금액 계산 (수수료 차감)
    const sellAmountBeforeFee = currentPrice * stockQuantity;
    const sellFee = sellAmountBeforeFee * commissionRate;
    const sellAmount = sellAmountBeforeFee - sellFee;
    
    // 수익 금액 계산
    const profitAmount = sellAmount - buyAmount;
    
    // 수익률 계산
    const profitRate = (profitAmount / buyAmount) * 100;
    
    // 결과 표시
    document.getElementById('buyAmount').textContent = buyAmount.toLocaleString('ko-KR') + ' 원';
    document.getElementById('sellAmount').textContent = sellAmount.toLocaleString('ko-KR') + ' 원';
    document.getElementById('profitAmount').textContent = profitAmount.toLocaleString('ko-KR') + ' 원';
    document.getElementById('profitRate').textContent = profitRate.toFixed(2) + ' %';
    
    // 수익/손실에 따른 색상 적용
    if (profitAmount > 0) {
        document.getElementById('profitAmount').classList.add('positive');
        document.getElementById('profitAmount').classList.remove('negative');
        document.getElementById('profitRate').classList.add('positive');
        document.getElementById('profitRate').classList.remove('negative');
    } else {
        document.getElementById('profitAmount').classList.add('negative');
        document.getElementById('profitAmount').classList.remove('positive');
        document.getElementById('profitRate').classList.add('negative');
        document.getElementById('profitRate').classList.remove('positive');
    }
    
    // 계산기 폼 숨기고 결과 표시
    document.getElementById('calculatorForm').style.display = 'none';
    document.getElementById('resultContainer').style.display = 'block';
}

// 초기화 및 다시 계산하기
function resetForm() {
    // 입력 필드 초기화
    document.getElementById('avgBuyPrice').value = '';
    document.getElementById('stockQuantity').value = '';
    document.getElementById('currentPrice').value = '';
    document.getElementById('brokerSelect').value = 'direct';
    document.getElementById('commission').value = '';
    
    // 증권사 선택에 따라 수수료 입력란 상태 업데이트
    updateCommission();
    
    // 계산기 폼 다시 표시하고 결과 숨기기
    document.getElementById('calculatorForm').style.display = 'block';
    document.getElementById('resultContainer').style.display = 'none';
    
    // 스크롤을 증권 종류 선택 필드로 이동
    const marketTypeElement = document.getElementById('marketType');
    marketTypeElement.scrollIntoView({ behavior: 'smooth' });
}

// 테마 변경 함수 (추가 기능으로 남겨둠)
function changeTheme(theme) {
    const stockCalc = document.getElementById('stockCalc');
    
    // 기존 테마 클래스 제거
    stockCalc.classList.remove('theme1', 'theme2', 'theme3');
    
    // 선택한 테마에 따라 CSS 변수 설정
    if (theme === 'theme2') {
        // 다크 테마
        stockCalc.style.setProperty('--primary', '#1e293b');
        stockCalc.style.setProperty('--secondary', '#0f172a');
        stockCalc.style.setProperty('--accent', '#38bdf8');
        stockCalc.style.setProperty('--background', '#0f172a');
        stockCalc.style.setProperty('--card-bg', '#1e293b');
        stockCalc.style.setProperty('--text', '#e2e8f0');
        stockCalc.style.setProperty('--header-gradient', 'linear-gradient(135deg, #334155, #0f172a)');
        stockCalc.style.setProperty('--header-text', '#f8fafc');
        stockCalc.style.setProperty('--profit', '#f87171');
        stockCalc.style.setProperty('--loss', '#60a5fa');
    } else if (theme === 'theme3') {
        // 네온 테마
        stockCalc.style.setProperty('--primary', '#6b21a8');
        stockCalc.style.setProperty('--secondary', '#581c87');
        stockCalc.style.setProperty('--accent', '#d946ef');
        stockCalc.style.setProperty('--background', '#f5f3ff');
        stockCalc.style.setProperty('--card-bg', '#ffffff');
        stockCalc.style.setProperty('--text', '#1e1b4b');
        stockCalc.style.setProperty('--header-gradient', 'linear-gradient(135deg, #8b5cf6, #d946ef)');
        stockCalc.style.setProperty('--header-text', '#ffffff');
        stockCalc.style.setProperty('--profit', '#10b981');
        stockCalc.style.setProperty('--loss', '#3b82f6');
    } else {
        // 기본 메탈실버 테마 (theme1)
        stockCalc.style.setProperty('--primary', '#7a7a7a');
        stockCalc.style.setProperty('--secondary', '#5a5a5a');
        stockCalc.style.setProperty('--accent', '#4a4a4a');
        stockCalc.style.setProperty('--background', '#e6e6e6');
        stockCalc.style.setProperty('--card-bg', '#f5f5f5');
        stockCalc.style.setProperty('--text', '#2c2c2c');
        stockCalc.style.setProperty('--header-gradient', 'linear-gradient(135deg, #c0c0c0, #8e8e8e)');
        stockCalc.style.setProperty('--header-text', '#ffffff');
        stockCalc.style.setProperty('--profit', '#ef4444');
        stockCalc.style.setProperty('--loss', '#3b82f6');
    }
    
    // 테마 클래스 추가
    stockCalc.classList.add(theme);
}